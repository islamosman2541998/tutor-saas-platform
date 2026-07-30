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
    'group_id', 'scheduled_date', 'expected_start_time', 'expected_end_time',
    'title', 'lesson_topic', 'notes', 'status', 'cancellation_reason',
])]
class ClassSession extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'actual_started_at' => 'datetime',
            'actual_closed_at' => 'datetime',
            'qr_expires_at' => 'datetime',
            'qr_is_active' => 'boolean',
            'attendance_is_locked' => 'boolean',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'scheduled' => 'مجدولة',
            'open' => 'مفتوحة',
            'completed' => 'مكتملة',
            default => 'ملغاة',
        };
    }
}
