<?php

namespace App\Livewire\Website;

use App\Actions\Website\SetWebsiteStatusAction;
use App\Actions\Website\UpdateWebsiteSettingsAction;
use App\Livewire\Concerns\Notifies;
use App\Models\WebsiteSettings;
use App\Support\Tenancy\TenantContext;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class WebsiteSettingsForm extends Component
{
    use Notifies, WithFileUploads;

    public string $site_name = '';

    public string $short_description = '';

    public $logo = null;

    public $favicon = null;

    public $teacher_image = null;

    public ?string $existingLogo = null;

    public ?string $existingFavicon = null;

    public ?string $existingTeacherImage = null;

    public string $primary_color = '#4f46e5';

    public string $secondary_color = '#10b981';

    public string $accent_color = '#f59e0b';

    public string $background_color = '#ffffff';

    public string $text_color = '#1e293b';

    public string $heading_color = '#0f172a';

    public string $button_color = '#4f46e5';

    public string $button_hover_color = '#4338ca';

    public string $navbar_color = '#ffffff';

    public string $footer_color = '#0f172a';

    public string $font_family = 'Cairo';

    public string $base_font_size = '16';

    public string $heading_font_size = '32';

    public string $border_radius = '12';

    public string $button_style = 'rounded';

    public bool $card_shadow = true;

    public string $section_spacing = 'normal';

    public string $container_width = 'normal';

    public string $maintenance_message = '';

    public $maintenance_image = null;

    public ?string $existingMaintenanceImage = null;

    public string $website_status = 'draft';

    public function mount(): void
    {
        $this->authorize('website.manage');

        $settings = WebsiteSettings::query()->first();

        if ($settings) {
            $this->site_name = (string) $settings->site_name;
            $this->short_description = (string) $settings->short_description;
            $this->existingLogo = $settings->logo;
            $this->existingFavicon = $settings->favicon;
            $this->existingTeacherImage = $settings->teacher_image;
            $this->primary_color = $settings->primary_color;
            $this->secondary_color = $settings->secondary_color;
            $this->accent_color = $settings->accent_color;
            $this->background_color = $settings->background_color;
            $this->text_color = $settings->text_color;
            $this->heading_color = $settings->heading_color;
            $this->button_color = $settings->button_color;
            $this->button_hover_color = $settings->button_hover_color;
            $this->navbar_color = $settings->navbar_color;
            $this->footer_color = $settings->footer_color;
            $this->font_family = $settings->font_family;
            $this->base_font_size = (string) $settings->base_font_size;
            $this->heading_font_size = (string) $settings->heading_font_size;
            $this->border_radius = (string) $settings->border_radius;
            $this->button_style = $settings->button_style;
            $this->card_shadow = $settings->card_shadow;
            $this->section_spacing = $settings->section_spacing;
            $this->container_width = $settings->container_width;
            $this->maintenance_message = (string) $settings->maintenance_message;
            $this->existingMaintenanceImage = $settings->maintenance_image;
        }

        $this->website_status = app(TenantContext::class)->get()->website_status;
    }

    public function save(UpdateWebsiteSettingsAction $action): void
    {
        $this->authorize('website.manage');

        $data = $this->validate([
            'site_name' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,ico,svg', 'max:512'],
            'teacher_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'primary_color' => ['required', 'string', 'max:20'],
            'secondary_color' => ['required', 'string', 'max:20'],
            'accent_color' => ['required', 'string', 'max:20'],
            'background_color' => ['required', 'string', 'max:20'],
            'text_color' => ['required', 'string', 'max:20'],
            'heading_color' => ['required', 'string', 'max:20'],
            'button_color' => ['required', 'string', 'max:20'],
            'button_hover_color' => ['required', 'string', 'max:20'],
            'navbar_color' => ['required', 'string', 'max:20'],
            'footer_color' => ['required', 'string', 'max:20'],
            'font_family' => ['required', 'string', 'max:100'],
            'base_font_size' => ['required', 'integer', 'min:10', 'max:24'],
            'heading_font_size' => ['required', 'integer', 'min:16', 'max:64'],
            'border_radius' => ['required', 'integer', 'min:0', 'max:32'],
            'button_style' => ['required', 'in:rounded,pill,square'],
            'card_shadow' => ['boolean'],
            'section_spacing' => ['required', 'in:compact,normal,relaxed'],
            'container_width' => ['required', 'in:narrow,normal,wide'],
            'maintenance_message' => ['nullable', 'string', 'max:1000'],
            'maintenance_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], [
            'site_name' => 'اسم الموقع',
            'primary_color' => 'اللون الأساسي',
        ]);

        $files = [
            'logo' => $this->logo,
            'favicon' => $this->favicon,
            'teacher_image' => $this->teacher_image,
            'maintenance_image' => $this->maintenance_image,
        ];

        foreach (['logo', 'favicon', 'teacher_image', 'maintenance_image'] as $field) {
            unset($data[$field]);
        }

        $action->execute($data, $files);

        $this->reset(['logo', 'favicon', 'teacher_image', 'maintenance_image']);
        $this->toast('تم حفظ إعدادات الموقع التعريفي.');
    }

    public function setStatus(string $status, SetWebsiteStatusAction $action): void
    {
        $this->authorize('website.manage');

        if (! in_array($status, ['draft', 'published', 'maintenance'], true)) {
            return;
        }

        $tenant = $action->execute(app(TenantContext::class)->get(), $status);
        $this->website_status = $tenant->website_status;

        $this->toast(match ($status) {
            'published' => 'تم نشر الموقع — أصبح مرئيًا للزوار.',
            'maintenance' => 'تم وضع الموقع تحت الصيانة.',
            default => 'تم إرجاع الموقع لوضع المسودة (غير مرئي للزوار).',
        });
    }

    public function render()
    {
        return view('livewire.website.website-settings-form');
    }
}
