<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'group_id', 'requested_at', 'priority', 'notes', 'status'])]
class WaitingListEntry extends Model
{
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
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

    public function statusLabel(): string
    {
        return match ($this->status) {
            'waiting' => 'بالانتظار',
            'contacted' => 'تم التواصل',
            'accepted' => 'مقبول',
            default => 'ملغي',
        };
    }
}
