<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name', 'phone', 'email', 'guardian_name', 'guardian_phone', 'guardian_email',
    'date_of_birth', 'gender', 'school_name', 'country', 'governorate', 'city', 'area',
    'address', 'image', 'notes', 'status', 'joined_at',
])]
class Student extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'joined_at' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function activeEnrollments(): HasMany
    {
        return $this->enrollments()->where('status', 'active');
    }

    public function waitingListEntries(): HasMany
    {
        return $this->hasMany(WaitingListEntry::class);
    }

    public function monthlyDues(): HasMany
    {
        return $this->hasMany(MonthlyDue::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function primaryContactPhone(): ?string
    {
        return $this->phone ?: $this->guardian_phone;
    }

    public function whatsappUrl(): ?string
    {
        $phone = $this->primaryContactPhone();

        if (! $phone) {
            return null;
        }

        $normalized = preg_replace('/\D/', '', $phone);

        return "https://wa.me/{$normalized}";
    }

    public function statusLabel(): string
    {
        return self::statusLabelFor($this->status);
    }

    public static function statusLabelFor(string $status): string
    {
        return match ($status) {
            'active' => 'نشط',
            'paused' => 'موقوف',
            'graduated' => 'متخرج',
            'withdrawn' => 'منسحب',
            default => 'مؤرشف',
        };
    }
}
