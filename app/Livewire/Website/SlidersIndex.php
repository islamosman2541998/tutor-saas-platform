<?php

namespace App\Livewire\Website;

use App\Actions\Website\DeleteSliderAction;
use App\Actions\Website\SaveSliderAction;
use App\Livewire\Concerns\Notifies;
use App\Models\Slider;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class SlidersIndex extends Component
{
    use Notifies, WithFileUploads;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $description = '';

    public string $button_text = '';

    public string $button_url = '';

    public bool $open_in_new_tab = false;

    public bool $is_active = true;

    public string $start_date = '';

    public string $end_date = '';

    public $image = null;

    public $mobile_image = null;

    public ?string $existingImage = null;

    public ?string $existingMobileImage = null;

    public function mount(): void
    {
        $this->authorize('website.manage');
    }

    public function create(): void
    {
        $this->authorize('website.manage');

        $this->reset([
            'editingId', 'title', 'description', 'button_text', 'button_url', 'open_in_new_tab',
            'start_date', 'end_date', 'image', 'mobile_image', 'existingImage', 'existingMobileImage',
        ]);
        $this->is_active = true;
        $this->showForm = true;
    }

    public function edit(int $sliderId): void
    {
        $this->authorize('website.manage');

        $slider = Slider::query()->findOrFail($sliderId);
        $this->editingId = $slider->id;
        $this->title = (string) $slider->title;
        $this->description = (string) $slider->description;
        $this->button_text = (string) $slider->button_text;
        $this->button_url = (string) $slider->button_url;
        $this->open_in_new_tab = $slider->open_in_new_tab;
        $this->is_active = $slider->is_active;
        $this->start_date = optional($slider->start_date)->toDateString() ?? '';
        $this->end_date = optional($slider->end_date)->toDateString() ?? '';
        $this->existingImage = $slider->image;
        $this->existingMobileImage = $slider->mobile_image;
        $this->image = null;
        $this->mobile_image = null;
        $this->showForm = true;
    }

    public function save(SaveSliderAction $action): void
    {
        $this->authorize('website.manage');

        $slider = $this->editingId ? Slider::query()->findOrFail($this->editingId) : null;

        $data = $this->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'button_url' => ['nullable', 'required_with:button_text', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'image' => [$slider ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [], [
            'button_url' => 'رابط الزر',
            'image' => 'الصورة',
        ]);

        $image = $this->image;
        $mobileImage = $this->mobile_image;
        unset($data['image'], $data['mobile_image']);

        $data['open_in_new_tab'] = $this->open_in_new_tab;
        $data['is_active'] = $this->is_active;
        $data['start_date'] = $data['start_date'] ?: null;
        $data['end_date'] = $data['end_date'] ?: null;
        $data['button_text'] = $data['button_text'] ?: null;
        $data['button_url'] = $data['button_url'] ?: null;

        $action->execute($data, $slider, $image, $mobileImage);

        $this->toast($slider ? 'تم تحديث السلايد.' : 'تم إنشاء السلايد.');
        $this->showForm = false;
    }

    public function askDelete(int $sliderId): void
    {
        $slider = Slider::query()->findOrFail($sliderId);

        $this->confirmThen(
            confirmEvent: 'slider-delete-confirmed',
            title: 'حذف السلايد؟',
            text: 'سيتم حذف هذا السلايد نهائيًا.',
            params: ['sliderId' => $sliderId],
            confirmButtonText: 'حذف',
        );
    }

    #[On('slider-delete-confirmed')]
    public function delete(int $sliderId, DeleteSliderAction $action): void
    {
        $this->authorize('website.manage');

        $action->execute(Slider::query()->findOrFail($sliderId));
        $this->toast('تم حذف السلايد.');
    }

    public function moveUp(int $sliderId): void
    {
        $this->swapSortOrder($sliderId, 'up');
    }

    public function moveDown(int $sliderId): void
    {
        $this->swapSortOrder($sliderId, 'down');
    }

    protected function swapSortOrder(int $sliderId, string $direction): void
    {
        $this->authorize('website.manage');

        $sliders = Slider::query()->orderBy('sort_order')->get();
        $index = $sliders->search(fn ($s) => $s->id === $sliderId);

        $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || ! $sliders->has($targetIndex)) {
            return;
        }

        $current = $sliders[$index];
        $target = $sliders[$targetIndex];

        [$currentOrder, $targetOrder] = [$current->sort_order, $target->sort_order];
        $current->update(['sort_order' => $targetOrder]);
        $target->update(['sort_order' => $currentOrder]);
    }

    public function render()
    {
        return view('livewire.website.sliders-index', [
            'sliders' => Slider::query()->orderBy('sort_order')->get(),
        ]);
    }
}
