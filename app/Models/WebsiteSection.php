<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['section_key', 'title', 'subtitle', 'is_visible', 'sort_order'])]
class WebsiteSection extends Model
{
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    /**
     * The fixed catalogue of sections a homepage can have, in their default
     * order. "ready" sections have a real content source already built;
     * the rest stay manageable (title/order/visibility) but render nothing
     * publicly yet — see PublicWebsiteController.
     */
    public const DEFAULTS = [
        'hero_slider' => ['label' => 'السلايدر الرئيسي', 'default_title' => null, 'ready' => true],
        'about' => ['label' => 'نبذة عن المدرّس', 'default_title' => 'نبذة عني', 'ready' => true],
        'stats' => ['label' => 'إحصائيات', 'default_title' => 'أرقام تتحدث عنّا', 'ready' => true],
        'subjects_grades' => ['label' => 'المواد والمراحل', 'default_title' => 'المواد والمراحل الدراسية', 'ready' => true],
        'groups' => ['label' => 'المجموعات المتاحة', 'default_title' => 'مجموعاتنا الحالية', 'ready' => true],
        'tips' => ['label' => 'نصائح ومقالات', 'default_title' => 'نصائح تهمّك', 'ready' => true],
        'testimonials' => ['label' => 'آراء الطلاب', 'default_title' => 'ماذا يقول طلابنا', 'ready' => true],
        'join_form' => ['label' => 'نموذج انضمام', 'default_title' => 'انضم إلينا', 'ready' => false],
        'contact' => ['label' => 'تواصل معنا', 'default_title' => 'تواصل معنا', 'ready' => true],
    ];

    /**
     * Ensures every canonical section_key has a row for the current tenant
     * — lazy sync instead of a registration-time hook, so tenants created
     * before this feature existed still get sensible defaults on first
     * visit to the sections page, with default ordering matching DEFAULTS.
     */
    public static function syncDefaults(): void
    {
        $existingKeys = static::query()->pluck('section_key')->all();

        foreach (array_keys(static::DEFAULTS) as $index => $key) {
            if (in_array($key, $existingKeys, true)) {
                continue;
            }

            static::query()->create([
                'section_key' => $key,
                'sort_order' => $index,
            ]);
        }
    }

    public function label(): string
    {
        return self::DEFAULTS[$this->section_key]['label'] ?? $this->section_key;
    }

    public function isReady(): bool
    {
        return self::DEFAULTS[$this->section_key]['ready'] ?? false;
    }

    public function displayTitle(): ?string
    {
        return $this->title ?: (self::DEFAULTS[$this->section_key]['default_title'] ?? null);
    }
}
