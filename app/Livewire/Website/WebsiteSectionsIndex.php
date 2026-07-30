<?php

namespace App\Livewire\Website;

use App\Livewire\Concerns\Notifies;
use App\Models\WebsiteSection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class WebsiteSectionsIndex extends Component
{
    use Notifies;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $subtitle = '';

    public function mount(): void
    {
        $this->authorize('website.manage');

        WebsiteSection::syncDefaults();
    }

    public function edit(int $sectionId): void
    {
        $this->authorize('website.manage');

        $section = WebsiteSection::query()->findOrFail($sectionId);
        $this->editingId = $section->id;
        $this->title = (string) $section->title;
        $this->subtitle = (string) $section->subtitle;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize('website.manage');

        $data = $this->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
        ]);

        WebsiteSection::query()->findOrFail($this->editingId)->update([
            'title' => $data['title'] ?: null,
            'subtitle' => $data['subtitle'] ?: null,
        ]);

        $this->toast('تم تحديث القسم.');
        $this->showForm = false;
    }

    public function toggleVisibility(int $sectionId): void
    {
        $this->authorize('website.manage');

        $section = WebsiteSection::query()->findOrFail($sectionId);
        $section->update(['is_visible' => ! $section->is_visible]);
    }

    public function moveUp(int $sectionId): void
    {
        $this->swapSortOrder($sectionId, 'up');
    }

    public function moveDown(int $sectionId): void
    {
        $this->swapSortOrder($sectionId, 'down');
    }

    protected function swapSortOrder(int $sectionId, string $direction): void
    {
        $this->authorize('website.manage');

        $sections = WebsiteSection::query()->orderBy('sort_order')->get();
        $index = $sections->search(fn ($s) => $s->id === $sectionId);

        $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || ! $sections->has($targetIndex)) {
            return;
        }

        $current = $sections[$index];
        $target = $sections[$targetIndex];

        [$currentOrder, $targetOrder] = [$current->sort_order, $target->sort_order];
        $current->update(['sort_order' => $targetOrder]);
        $target->update(['sort_order' => $currentOrder]);
    }

    public function render()
    {
        return view('livewire.website.website-sections-index', [
            'sections' => WebsiteSection::query()->orderBy('sort_order')->get(),
        ]);
    }
}
