<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'image', 'mobile_image', 'title', 'description', 'button_text', 'button_url',
    'open_in_new_tab', 'sort_order', 'is_active', 'start_date', 'end_date',
])]
class Slider extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function imageUrl(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function mobileImageUrl(): ?string
    {
        return $this->mobile_image ? Storage::disk('public')->url($this->mobile_image) : $this->imageUrl();
    }

    /**
     * Active flag plus the optional scheduling window — a slide can be
     * prepared ahead of time (start_date in the future) or left running
     * indefinitely (both dates null).
     */
    public function scopeCurrentlyRunning($query)
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $today))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $today));
    }
}
