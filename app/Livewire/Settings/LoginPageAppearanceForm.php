<?php

namespace App\Livewire\Settings;

use App\Actions\Settings\UpdateLoginPageSettingsAction;
use App\Livewire\Concerns\Notifies;
use App\Models\LoginPageSettings;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class LoginPageAppearanceForm extends Component
{
    use Notifies, WithFileUploads;

    public string $background_color = '#f8fafc';

    public bool $overlay_enabled = true;

    public string $overlay_opacity = '0.5';

    public string $card_position = 'center';

    public string $card_bg_color = '#ffffff';

    public string $card_opacity = '1';

    public bool $card_blur = false;

    public string $border_radius = '16';

    public string $shadow_style = 'medium';

    public string $theme_mode = 'light';

    public string $title_color = '#0f172a';

    public string $text_color = '#475569';

    public string $label_color = '#334155';

    public string $input_bg_color = '#ffffff';

    public string $input_text_color = '#0f172a';

    public string $input_border_color = '#cbd5e1';

    public string $input_focus_color = '#4f46e5';

    public string $button_color = '#4f46e5';

    public string $button_text_color = '#ffffff';

    public string $button_hover_color = '#4338ca';

    public bool $show_logo = true;

    public string $welcome_title = '';

    public string $welcome_description = '';

    public bool $remember_me_enabled = true;

    public bool $forgot_password_enabled = true;

    public $background_image = null;

    public $side_image = null;

    public ?string $existingBackgroundImage = null;

    public ?string $existingSideImage = null;

    public function mount(): void
    {
        $this->authorize('settings.manage');

        $settings = LoginPageSettings::query()->first();

        if ($settings) {
            $this->background_color = $settings->background_color;
            $this->overlay_enabled = $settings->overlay_enabled;
            $this->overlay_opacity = (string) $settings->overlay_opacity;
            $this->card_position = $settings->card_position;
            $this->card_bg_color = $settings->card_bg_color;
            $this->card_opacity = (string) $settings->card_opacity;
            $this->card_blur = $settings->card_blur;
            $this->border_radius = (string) $settings->border_radius;
            $this->shadow_style = $settings->shadow_style;
            $this->theme_mode = $settings->theme_mode;
            $this->title_color = $settings->title_color;
            $this->text_color = $settings->text_color;
            $this->label_color = $settings->label_color;
            $this->input_bg_color = $settings->input_bg_color;
            $this->input_text_color = $settings->input_text_color;
            $this->input_border_color = $settings->input_border_color;
            $this->input_focus_color = $settings->input_focus_color;
            $this->button_color = $settings->button_color;
            $this->button_text_color = $settings->button_text_color;
            $this->button_hover_color = $settings->button_hover_color;
            $this->show_logo = $settings->show_logo;
            $this->welcome_title = (string) $settings->welcome_title;
            $this->welcome_description = (string) $settings->welcome_description;
            $this->remember_me_enabled = $settings->remember_me_enabled;
            $this->forgot_password_enabled = $settings->forgot_password_enabled;
            $this->existingBackgroundImage = $settings->background_image;
            $this->existingSideImage = $settings->side_image;
        }
    }

    public function save(UpdateLoginPageSettingsAction $action): void
    {
        $this->authorize('settings.manage');

        $data = $this->validate([
            'background_color' => ['required', 'string', 'max:20'],
            'overlay_opacity' => ['required', 'numeric', 'min:0', 'max:1'],
            'card_position' => ['required', 'in:center,left,right'],
            'card_bg_color' => ['required', 'string', 'max:20'],
            'card_opacity' => ['required', 'numeric', 'min:0', 'max:1'],
            'border_radius' => ['required', 'integer', 'min:0', 'max:32'],
            'shadow_style' => ['required', 'in:none,soft,medium,strong'],
            'theme_mode' => ['required', 'in:light,dark,user_choice'],
            'title_color' => ['required', 'string', 'max:20'],
            'text_color' => ['required', 'string', 'max:20'],
            'label_color' => ['required', 'string', 'max:20'],
            'input_bg_color' => ['required', 'string', 'max:20'],
            'input_text_color' => ['required', 'string', 'max:20'],
            'input_border_color' => ['required', 'string', 'max:20'],
            'input_focus_color' => ['required', 'string', 'max:20'],
            'button_color' => ['required', 'string', 'max:20'],
            'button_text_color' => ['required', 'string', 'max:20'],
            'button_hover_color' => ['required', 'string', 'max:20'],
            'welcome_title' => ['nullable', 'string', 'max:255'],
            'welcome_description' => ['nullable', 'string', 'max:500'],
            'background_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'side_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $files = [
            'background_image' => $this->background_image,
            'side_image' => $this->side_image,
        ];

        foreach (['background_image', 'side_image'] as $field) {
            unset($data[$field]);
        }

        $data['overlay_enabled'] = $this->overlay_enabled;
        $data['card_blur'] = $this->card_blur;
        $data['show_logo'] = $this->show_logo;
        $data['remember_me_enabled'] = $this->remember_me_enabled;
        $data['forgot_password_enabled'] = $this->forgot_password_enabled;
        $data['welcome_title'] = $data['welcome_title'] ?: null;
        $data['welcome_description'] = $data['welcome_description'] ?: null;

        $action->execute($data, $files);

        $this->reset(['background_image', 'side_image']);
        $this->toast('تم حفظ مظهر صفحة تسجيل الدخول.');
    }

    public function render()
    {
        return view('livewire.settings.login-page-appearance-form');
    }
}
