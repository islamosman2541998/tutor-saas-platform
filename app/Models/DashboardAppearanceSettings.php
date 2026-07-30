<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'primary_color', 'secondary_color', 'sidebar_bg_color', 'sidebar_text_color', 'sidebar_active_color',
    'topbar_color', 'page_bg_color', 'card_bg_color', 'text_color', 'border_radius',
    'sidebar_size', 'theme_mode', 'logo_full', 'logo_mini', 'favicon',
])]
class DashboardAppearanceSettings extends Model
{
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'border_radius' => 'integer',
        ];
    }

    public function logoFullUrl(): ?string
    {
        return $this->logo_full ? Storage::disk('public')->url($this->logo_full) : null;
    }

    public function logoMiniUrl(): ?string
    {
        return $this->logo_mini ? Storage::disk('public')->url($this->logo_mini) : null;
    }

    public function faviconUrl(): ?string
    {
        return $this->favicon ? Storage::disk('public')->url($this->favicon) : null;
    }

    public function sidebarWidthClass(): string
    {
        return match ($this->sidebar_size) {
            'compact' => 'lg:w-56',
            'wide' => 'lg:w-80',
            default => 'lg:w-72',
        };
    }

    public function sidebarMarginClass(): string
    {
        return match ($this->sidebar_size) {
            'compact' => 'lg:mr-56',
            'wide' => 'lg:mr-80',
            default => 'lg:mr-72',
        };
    }

    /**
     * @return array<string, string>
     */
    public function cssVariables(): array
    {
        return [
            '--dash-primary' => $this->primary_color,
            '--dash-secondary' => $this->secondary_color,
            '--dash-sidebar-bg' => $this->sidebar_bg_color,
            '--dash-sidebar-text' => $this->sidebar_text_color,
            '--dash-sidebar-active' => $this->sidebar_active_color,
            '--dash-topbar' => $this->topbar_color,
            '--dash-page-bg' => $this->page_bg_color,
            '--dash-card-bg' => $this->card_bg_color,
            '--dash-text' => $this->text_color,
            '--dash-radius' => "{$this->border_radius}px",
        ];
    }

    /**
     * Sensible defaults matching the current hardcoded design — used when a
     * tenant hasn't saved any customization yet, so the dashboard renders
     * identically to before this feature existed.
     */
    public static function defaults(): self
    {
        return new self([
            'primary_color' => '#4f46e5',
            'secondary_color' => '#10b981',
            'sidebar_bg_color' => '#ffffff',
            'sidebar_text_color' => '#334155',
            'sidebar_active_color' => '#4f46e5',
            'topbar_color' => '#ffffff',
            'page_bg_color' => '#f8fafc',
            'card_bg_color' => '#ffffff',
            'text_color' => '#1e293b',
            'border_radius' => 16,
            'sidebar_size' => 'normal',
            'theme_mode' => 'light',
        ]);
    }
}
