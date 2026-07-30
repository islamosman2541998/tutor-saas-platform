<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['platform', 'url', 'is_active', 'sort_order', 'display_location'])]
class SocialLink extends Model
{
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function platformLabel(): string
    {
        return match ($this->platform) {
            'facebook' => 'فيسبوك',
            'instagram' => 'إنستجرام',
            'tiktok' => 'تيك توك',
            'youtube' => 'يوتيوب',
            'whatsapp' => 'واتساب',
            'telegram' => 'تليجرام',
            'linkedin' => 'لينكدإن',
            'x' => 'X (تويتر)',
            default => 'أخرى',
        };
    }

    public function displayLocationLabel(): string
    {
        return match ($this->display_location) {
            'navbar' => 'الشريط العلوي',
            'footer' => 'الفوتر',
            'contact' => 'قسم التواصل',
            default => 'كل الأماكن',
        };
    }

    public function scopeVisibleIn($query, string $location)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->where('display_location', $location)->orWhere('display_location', 'all'));
    }
}
