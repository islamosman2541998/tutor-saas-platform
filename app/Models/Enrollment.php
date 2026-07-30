<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'student_id', 'group_id', 'academic_year_id', 'joined_at', 'left_at',
    'default_monthly_price', 'custom_monthly_price', 'discount_type', 'discount_value',
    'final_monthly_price', 'payment_due_day', 'status', 'withdrawal_reason', 'notes',
])]
class Enrollment extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'left_at' => 'date',
            'default_monthly_price' => 'decimal:2',
            'custom_monthly_price' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'final_monthly_price' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function monthlyDues(): HasMany
    {
        return $this->hasMany(MonthlyDue::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'active' => 'نشط',
            'paused' => 'موقوف',
            'withdrawn' => 'منسحب',
            'completed' => 'مكتمل',
            default => 'منقول',
        };
    }
}
