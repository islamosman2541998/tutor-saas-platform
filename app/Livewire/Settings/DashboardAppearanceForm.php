<?php

namespace App\Livewire\Settings;

use App\Actions\Settings\UpdateDashboardAppearanceAction;
use App\Livewire\Concerns\Notifies;
use App\Models\DashboardAppearanceSettings;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class DashboardAppearanceForm extends Component
{
    use Notifies, WithFileUploads;

    public string $primary_color = '#4f46e5';

    public string $secondary_color = '#10b981';

    public string $sidebar_bg_color = '#ffffff';

    public string $sidebar_text_color = '#334155';

    public string $sidebar_active_color = '#4f46e5';

    public string $topbar_color = '#ffffff';

    public string $page_bg_color = '#f8fafc';

    public string $card_bg_color = '#ffffff';

    public string $text_color = '#1e293b';

    public string $border_radius = '16';

    public string $sidebar_size = 'normal';

    public string $theme_mode = 'light';

    public $logo_full = null;

    public $logo_mini = null;

    public $favicon = null;

    public ?string $existingLogoFull = null;

    public ?string $existingLogoMini = null;

    public ?string $existingFavicon = null;

    public function mount(): void
    {
        $this->authorize('settings.manage');

        $settings = DashboardAppearanceSettings::query()->first();

        if ($settings) {
            $this->primary_color = $settings->primary_color;
            $this->secondary_color = $settings->secondary_color;
            $this->sidebar_bg_color = $settings->sidebar_bg_color;
            $this->sidebar_text_color = $settings->sidebar_text_color;
            $this->sidebar_active_color = $settings->sidebar_active_color;
            $this->topbar_color = $settings->topbar_color;
            $this->page_bg_color = $settings->page_bg_color;
            $this->card_bg_color = $settings->card_bg_color;
            $this->text_color = $settings->text_color;
            $this->border_radius = (string) $settings->border_radius;
            $this->sidebar_size = $settings->sidebar_size;
            $this->theme_mode = $settings->theme_mode;
            $this->existingLogoFull = $settings->logo_full;
            $this->existingLogoMini = $settings->logo_mini;
            $this->existingFavicon = $settings->favicon;
        }
    }

    public function resetToDefaults(): void
    {
        $this->authorize('settings.manage');

        $defaults = DashboardAppearanceSettings::defaults();

        $this->primary_color = $defaults->primary_color;
        $this->secondary_color = $defaults->secondary_color;
        $this->sidebar_bg_color = $defaults->sidebar_bg_color;
        $this->sidebar_text_color = $defaults->sidebar_text_color;
        $this->sidebar_active_color = $defaults->sidebar_active_color;
        $this->topbar_color = $defaults->topbar_color;
        $this->page_bg_color = $defaults->page_bg_color;
        $this->card_bg_color = $defaults->card_bg_color;
        $this->text_color = $defaults->text_color;
        $this->border_radius = (string) $defaults->border_radius;
        $this->sidebar_size = $defaults->sidebar_size;
        $this->theme_mode = $defaults->theme_mode;
    }

    public function save(UpdateDashboardAppearanceAction $action): void
    {
        $this->authorize('settings.manage');

        $data = $this->validate([
            'primary_color' => ['required', 'string', 'max:20'],
            'secondary_color' => ['required', 'string', 'max:20'],
            'sidebar_bg_color' => ['required', 'string', 'max:20'],
            'sidebar_text_color' => ['required', 'string', 'max:20'],
            'sidebar_active_color' => ['required', 'string', 'max:20'],
            'topbar_color' => ['required', 'string', 'max:20'],
            'page_bg_color' => ['required', 'string', 'max:20'],
            'card_bg_color' => ['required', 'string', 'max:20'],
            'text_color' => ['required', 'string', 'max:20'],
            'border_radius' => ['required', 'integer', 'min:0', 'max:32'],
            'sidebar_size' => ['required', 'in:compact,normal,wide'],
            'theme_mode' => ['required', 'in:light,dark,user_choice'],
            'logo_full' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'logo_mini' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:1024'],
            'favicon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,ico,svg', 'max:512'],
        ]);

        $files = [
            'logo_full' => $this->logo_full,
            'logo_mini' => $this->logo_mini,
            'favicon' => $this->favicon,
        ];

        foreach (['logo_full', 'logo_mini', 'favicon'] as $field) {
            unset($data[$field]);
        }

        $action->execute($data, $files);

        $this->reset(['logo_full', 'logo_mini', 'favicon']);
        $this->toast('تم حفظ مظهر لوحة التحكم — أعد تحميل أي صفحة أخرى لرؤية التغيير كاملًا.');
    }

    public function render()
    {
        return view('livewire.settings.dashboard-appearance-form');
    }
}
