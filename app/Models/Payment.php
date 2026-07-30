<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id', 'enrollment_id', 'monthly_due_id', 'amount', 'payment_method', 'paid_at',
    'reference_number', 'attachment', 'notes', 'status',
    'cancelled_at', 'cancellation_reason',
])]
class Payment extends Model
{
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function monthlyDue(): BelongsTo
    {
        return $this->belongsTo(MonthlyDue::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function methodLabel(): string
    {
        return match ($this->payment_method) {
            'cash' => 'نقدًا',
            'bank_transfer' => 'تحويل بنكي',
            'wallet' => 'محفظة إلكترونية',
            'card' => 'بطاقة',
            default => 'أخرى',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'completed' => 'مكتملة',
            'cancelled' => 'ملغاة',
            default => 'مُرجَعة',
        };
    }
}
