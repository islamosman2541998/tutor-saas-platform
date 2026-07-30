<?php

namespace App\Actions\Billing;

use App\Actions\Notifications\SendTenantNotificationAction;
use App\Exceptions\CannotPerformActionException;
use App\Models\MonthlyDue;
use App\Models\Payment;
use App\Models\Student;
use App\Notifications\PaymentReceivedNotification;
use App\Support\Activity\TenantActivity;
use App\Support\Files\TenantUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Records one or more payments for a student in a single submission — a
 * full settlement of one due, a partial payment against one due, or several
 * dues (different months) paid at once. Each due gets its own Payment row
 * (the schema ties one payment to exactly one monthly_due_id), all sharing
 * the same method/date/reference/attachment so they read as one visit at
 * the register even though they're separate ledger entries.
 */
class RecordPaymentAction
{
    public function __construct(
        protected TenantUploads $uploads,
        protected SendTenantNotificationAction $notifier,
    ) {}

    /**
     * @param  array<int, array{monthly_due_id: int, amount: float}>  $items
     * @return Collection<int, Payment>
     */
    public function execute(Student $student, array $items, array $meta, ?UploadedFile $attachment = null): Collection
    {
        if (empty($items)) {
            throw new CannotPerformActionException('اختر استحقاقًا واحدًا على الأقل لتسجيل دفعة.');
        }

        $payments = DB::transaction(function () use ($student, $items, $meta, $attachment) {
            $attachmentPath = $attachment ? $this->uploads->store($attachment, 'payments') : null;
            $payments = new Collection;

            foreach ($items as $item) {
                $due = MonthlyDue::query()
                    ->where('student_id', $student->id)
                    ->whereKey($item['monthly_due_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (in_array($due->status, ['paid', 'waived', 'cancelled'], true)) {
                    throw new CannotPerformActionException(
                        'استحقاق '.MonthlyDue::monthLabel($due->billing_month)." {$due->billing_year} ".($due->status === 'paid' ? 'مدفوع بالكامل بالفعل.' : 'تمت تسويته ولا يقبل الدفع.')
                    );
                }

                $amount = round((float) $item['amount'], 2);

                if ($amount <= 0 || $amount > (float) $due->remaining_amount) {
                    throw new CannotPerformActionException(
                        'المبلغ المدخل غير صالح لاستحقاق '.MonthlyDue::monthLabel($due->billing_month)." {$due->billing_year} (المتبقي: {$due->remaining_amount})."
                    );
                }

                $payment = Payment::query()->forceCreate([
                    'student_id' => $student->id,
                    'enrollment_id' => $due->enrollment_id,
                    'monthly_due_id' => $due->id,
                    'receipt_number' => $this->generateReceiptNumber(),
                    'amount' => $amount,
                    'payment_method' => $meta['payment_method'],
                    'paid_at' => $meta['paid_at'],
                    'reference_number' => $meta['reference_number'] ?? null,
                    'attachment' => $attachmentPath,
                    'notes' => $meta['notes'] ?? null,
                    'status' => 'completed',
                    'created_by' => Auth::id(),
                ]);

                $newPaid = round((float) $due->paid_amount + $amount, 2);
                $newRemaining = max(0, round((float) $due->final_amount - $newPaid, 2));

                $due->update([
                    'paid_amount' => $newPaid,
                    'remaining_amount' => $newRemaining,
                    'status' => $newRemaining <= 0 ? 'paid' : 'partially_paid',
                ]);

                TenantActivity::log('تسجيل دفعة', $payment, [
                    'student' => $student->name,
                    'amount' => $amount,
                    'month' => MonthlyDue::monthLabel($due->billing_month),
                    'year' => $due->billing_year,
                    'receipt_number' => $payment->receipt_number,
                ]);

                $payments->push($payment);
            }

            return $payments;
        });

        // Dispatched after the transaction commits — the queue worker that
        // picks this job up runs in a separate process/connection and must
        // never see an uncommitted Payment row.
        $email = $student->email ?: $student->guardian_email;
        $phone = $student->phone ?: $student->guardian_phone;

        foreach ($payments as $payment) {
            $this->notifier->execute(new PaymentReceivedNotification($payment), $payment, $email, $phone);
        }

        return $payments;
    }

    protected function generateReceiptNumber(): string
    {
        $count = Payment::query()->count() + 1;
        $code = 'RCPT-'.str_pad((string) $count, 6, '0', STR_PAD_LEFT);

        while (Payment::query()->where('receipt_number', $code)->exists()) {
            $count++;
            $code = 'RCPT-'.str_pad((string) $count, 6, '0', STR_PAD_LEFT);
        }

        return $code;
    }
}
