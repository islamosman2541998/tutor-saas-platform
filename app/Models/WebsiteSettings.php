<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'site_name', 'logo', 'favicon', 'teacher_image', 'short_description',
    'primary_color', 'secondary_color', 'accent_color', 'background_color', 'text_color',
    'heading_color', 'button_color', 'button_hover_color', 'navbar_color', 'footer_color',
    'font_family', 'base_font_size', 'heading_font_size', 'border_radius', 'button_style',
    'card_shadow', 'section_spacing', 'container_width', 'maintenance_message', 'maintenance_image',
])]
class WebsiteSettings extends Model
{
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'card_shadow' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }

    public function faviconUrl(): ?string
    {
        return $this->favicon ? Storage::disk('public')->url($this->favicon) : null;
    }

    public function teacherImageUrl(): ?string
    {
        return $this->teacher_image ? Storage::disk('public')->url($this->teacher_image) : null;
    }

    public function maintenanceImageUrl(): ?string
    {
        return $this->maintenance_image ? Storage::disk('public')->url($this->maintenance_image) : null;
    }

    /**
     * CSS custom properties consumed by the public site layout — one place
     * so the dashboard preview and the live public site can never drift.
     */
    public function cssVariables(): array
    {
        return [
            '--site-primary' => $this->primary_color,
            '--site-secondary' => $this->secondary_color,
            '--site-accent' => $this->accent_color,
            '--site-bg' => $this->background_color,
            '--site-text' => $this->text_color,
            '--site-heading' => $this->heading_color,
            '--site-button' => $this->button_color,
            '--site-button-hover' => $this->button_hover_color,
            '--site-navbar' => $this->navbar_color,
            '--site-footer' => $this->footer_color,
            '--site-font' => $this->font_family,
            '--site-font-size' => "{$this->base_font_size}px",
            '--site-heading-size' => "{$this->heading_font_size}px",
            '--site-radius' => "{$this->border_radius}px",
        ];
    }

    public function buttonRadiusClass(): string
    {
        return match ($this->button_style) {
            'pill' => '9999px',
            'square' => '0px',
            default => "{$this->border_radius}px",
        };
    }
}
