<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['label', 'url', 'type', 'target_key', 'open_in_new_tab', 'is_visible', 'sort_order'])]
class NavbarItem extends Model
{
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'open_in_new_tab' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    /**
     * 'section' still resolves to an in-page anchor (home.blade.php's
     * section ids). 'page' resolves to a real Page route now that Pages
     * exist — target_key holds that page's slug. 'external' just uses the
     * raw URL as typed. Route generation needs the tenant slug, which
     * isn't available on a bare model, so callers rendering 'page' items
     * should prefer building the URL themselves (see the header
     * component) — this fallback only covers 'section'/'external'.
     */
    public function href(): string
    {
        return match ($this->type) {
            'external' => $this->url ?: '#',
            default => '#'.($this->target_key ?: ''),
        };
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'page' => 'صفحة',
            'external' => 'رابط خارجي',
            default => 'رابط من النظام',
        };
    }

    /**
     * The fixed catalogue of ready-made links a navbar item of type
     * 'section' can point to. Mirrors WebsiteSection::DEFAULTS (same labels,
     * same homepage anchors) plus 'home' and 'tips' — 'tips' is special: it
     * resolves to the standalone tips index page, not an anchor (see
     * header.blade.php), because a full listing page makes more sense in a
     * navbar than scrolling to the homepage preview section.
     */
    public static function systemLinkOptions(): array
    {
        return [
            'home' => 'الصفحة الرئيسية',
            'about' => 'نبذة عن المدرّس',
            'stats' => 'إحصائيات',
            'subjects_grades' => 'المواد والمراحل',
            'groups' => 'المجموعات المتاحة',
            'tips' => 'كل النصائح والمقالات',
            'testimonials' => 'آراء الطلاب',
            'contact' => 'تواصل معنا',
        ];
    }
}
