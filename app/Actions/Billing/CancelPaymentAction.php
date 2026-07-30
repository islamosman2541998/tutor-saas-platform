<?php

namespace App\Actions\Billing;

use App\Exceptions\CannotPerformActionException;
use App\Models\Payment;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Cancelling a payment never deletes it — it stays as a record of what was
 * received and then reversed — but it does reverse its effect on the
 * MonthlyDue it was applied to, recomputing paid/remaining/status exactly
 * as if that payment had never happened.
 */
class CancelPaymentAction
{
    public function execute(Payment $payment, string $reason): Payment
    {
        if ($payment->status !== 'completed') {
            throw new CannotPerformActionException('لا يمكن إلغاء دفعة ملغاة أو مُرجَعة بالفعل.');
        }

        return DB::transaction(function () use ($payment, $reason) {
            $due = $payment->monthlyDue()->lockForUpdate()->first();

            if ($due) {
                $newPaid = max(0, round((float) $due->paid_amount - (float) $payment->amount, 2));
                $newRemaining = round((float) $due->final_amount - $newPaid, 2);

                $due->update([
                    'paid_amount' => $newPaid,
                    'remaining_amount' => $newRemaining,
                    'status' => $newPaid <= 0 ? 'unpaid' : 'partially_paid',
                ]);
            }

            // cancelled_by is deliberately excluded from Payment's fillable
            // list — forceFill is required here since this is the one
            // legitimate place it's set.
            $payment->forceFill([
                'status' => 'cancelled',
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ])->save();

            TenantActivity::log('إلغاء دفعة', $payment, [
                'reason' => $reason,
                'amount' => $payment->amount,
                'receipt_number' => $payment->receipt_number,
            ]);

            return $payment->fresh();
        });
    }
}
