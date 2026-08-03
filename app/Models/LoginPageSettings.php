<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'background_image', 'background_color', 'overlay_enabled', 'overlay_opacity',
    'card_position', 'card_bg_color', 'card_opacity', 'card_blur', 'border_radius', 'shadow_style', 'theme_mode',
    'title_color', 'brand_name_color', 'heading_color', 'text_color', 'label_color', 'input_bg_color', 'input_text_color',
    'input_border_color', 'input_focus_color', 'button_color', 'button_text_color', 'button_hover_color',
    'show_logo', 'welcome_title', 'welcome_description', 'side_image',
    'remember_me_enabled', 'forgot_password_enabled',
])]
class LoginPageSettings extends Model
{
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'overlay_enabled' => 'boolean',
            'overlay_opacity' => 'float',
            'card_opacity' => 'float',
            'card_blur' => 'boolean',
            'border_radius' => 'integer',
            'show_logo' => 'boolean',
            'remember_me_enabled' => 'boolean',
            'forgot_password_enabled' => 'boolean',
        ];
    }

    public function backgroundImageUrl(): ?string
    {
        return $this->background_image ? Storage::disk('public')->url($this->background_image) : null;
    }

    public function sideImageUrl(): ?string
    {
        return $this->side_image ? Storage::disk('public')->url($this->side_image) : null;
    }

    public function shadowClass(): string
    {
        return match ($this->shadow_style) {
            'none' => 'none',
            'soft' => '0 2px 8px rgba(15, 23, 42, 0.06)',
            'strong' => '0 20px 50px rgba(15, 23, 42, 0.25)',
            default => '0 10px 25px rgba(15, 23, 42, 0.12)',
        };
    }

    /**
     * @return array<string, string>
     */
    public function cssVariables(): array
    {
        return [
            '--login-bg-color' => $this->background_color,
            '--login-overlay-opacity' => (string) ($this->overlay_enabled ? $this->overlay_opacity : 0),
            '--login-card-bg' => $this->card_bg_color,
            '--login-card-opacity' => (string) $this->card_opacity,
            '--login-radius' => "{$this->border_radius}px",
            '--login-shadow' => $this->shadowClass(),
            '--login-title' => $this->title_color,
            '--login-brand' => $this->brand_name_color,
            '--login-heading' => $this->heading_color,
            '--login-text' => $this->text_color,
            '--login-label' => $this->label_color,
            '--login-input-bg' => $this->input_bg_color,
            '--login-input-text' => $this->input_text_color,
            '--login-input-border' => $this->input_border_color,
            '--login-input-focus' => $this->input_focus_color,
            '--login-button' => $this->button_color,
            '--login-button-text' => $this->button_text_color,
            '--login-button-hover' => $this->button_hover_color,
        ];
    }

    public static function defaults(): self
    {
        return new self([
            'background_color' => '#f8fafc',
            'overlay_enabled' => true,
            'overlay_opacity' => 0.5,
            'card_position' => 'center',
            'card_bg_color' => '#ffffff',
            'card_opacity' => 1,
            'card_blur' => false,
            'border_radius' => 16,
            'shadow_style' => 'medium',
            'theme_mode' => 'light',
            'title_color' => '#0f172a',
            'brand_name_color' => '#0f172a',
            'heading_color' => '#0f172a',
            'text_color' => '#475569',
            'label_color' => '#334155',
            'input_bg_color' => '#ffffff',
            'input_text_color' => '#0f172a',
            'input_border_color' => '#cbd5e1',
            'input_focus_color' => '#4f46e5',
            'button_color' => '#4f46e5',
            'button_text_color' => '#ffffff',
            'button_hover_color' => '#4338ca',
            'show_logo' => true,
            'remember_me_enabled' => true,
            'forgot_password_enabled' => true,
        ]);
    }
}
