<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'student_id', 'enrollment_id', 'group_id', 'academic_year_id', 'billing_month', 'billing_year',
    'due_date', 'base_amount', 'discount_amount', 'final_amount', 'paid_amount', 'remaining_amount',
    'status', 'waived_reason', 'notes',
])]
class MonthlyDue extends Model
{
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'base_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'generated_automatically' => 'boolean',
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

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date->isPast()
            && $this->remaining_amount > 0
            && in_array($this->status, ['unpaid', 'partially_paid'], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'unpaid' => 'غير مدفوع',
            'partially_paid' => 'مدفوع جزئيًا',
            'paid' => 'مدفوع',
            'waived' => 'مُعفى',
            default => 'ملغي',
        };
    }

    public static function monthLabel(int $month): string
    {
        return [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
            7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ][$month] ?? (string) $month;
    }
}
