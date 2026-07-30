<?php

namespace App\Livewire\Website;

use App\Actions\Website\DeleteNavbarItemAction;
use App\Livewire\Concerns\Notifies;
use App\Models\NavbarItem;
use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class NavbarItemsIndex extends Component
{
    use Notifies;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $label = '';

    public string $type = 'section';

    public string $url = '';

    public string $target_key = '';

    public bool $open_in_new_tab = false;

    public bool $is_visible = true;

    public function mount(): void
    {
        $this->authorize('website.manage');
    }

    public function create(): void
    {
        $this->authorize('website.manage');

        $this->reset(['editingId', 'label', 'url', 'target_key', 'open_in_new_tab']);
        $this->type = 'section';
        $this->is_visible = true;
        $this->showForm = true;
    }

    public function edit(int $itemId): void
    {
        $this->authorize('website.manage');

        $item = NavbarItem::query()->findOrFail($itemId);
        $this->editingId = $item->id;
        $this->label = $item->label;
        $this->type = $item->type;
        $this->url = (string) $item->url;
        $this->target_key = (string) $item->target_key;
        $this->open_in_new_tab = $item->open_in_new_tab;
        $this->is_visible = $item->is_visible;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize('website.manage');

        $data = $this->validate([
            'label' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:page,section,external'],
            'url' => ['nullable', 'required_if:type,external', 'string', 'max:255'],
            'target_key' => ['nullable', 'required_if:type,page,section', 'string', 'max:100'],
        ], [], [
            'label' => 'التسمية',
            'url' => 'الرابط',
            'target_key' => 'المفتاح المستهدف',
        ]);

        $data['open_in_new_tab'] = $this->open_in_new_tab;
        $data['is_visible'] = $this->is_visible;
        $data['url'] = $data['url'] ?: null;
        $data['target_key'] = $data['target_key'] ?: null;

        if ($this->editingId) {
            NavbarItem::query()->findOrFail($this->editingId)->update($data);
            $this->toast('تم تحديث عنصر شريط التنقّل.');
        } else {
            $data['sort_order'] = (NavbarItem::query()->max('sort_order') ?? 0) + 1;
            NavbarItem::query()->create($data);
            $this->toast('تم إنشاء عنصر شريط التنقّل.');
        }

        $this->showForm = false;
    }

    public function askDelete(int $itemId): void
    {
        $item = NavbarItem::query()->findOrFail($itemId);

        $this->confirmThen(
            confirmEvent: 'navbar-item-delete-confirmed',
            title: 'حذف عنصر شريط التنقّل؟',
            text: "سيتم حذف \"{$item->label}\" نهائيًا.",
            params: ['itemId' => $itemId],
            confirmButtonText: 'حذف',
        );
    }

    #[On('navbar-item-delete-confirmed')]
    public function delete(int $itemId, DeleteNavbarItemAction $action): void
    {
        $this->authorize('website.manage');

        $action->execute(NavbarItem::query()->findOrFail($itemId));
        $this->toast('تم حذف عنصر شريط التنقّل.');
    }

    public function moveUp(int $itemId): void
    {
        $this->swapSortOrder($itemId, 'up');
    }

    public function moveDown(int $itemId): void
    {
        $this->swapSortOrder($itemId, 'down');
    }

    protected function swapSortOrder(int $itemId, string $direction): void
    {
        $this->authorize('website.manage');

        $items = NavbarItem::query()->orderBy('sort_order')->get();
        $index = $items->search(fn ($i) => $i->id === $itemId);

        $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || ! $items->has($targetIndex)) {
            return;
        }

        $current = $items[$index];
        $target = $items[$targetIndex];

        [$currentOrder, $targetOrder] = [$current->sort_order, $target->sort_order];
        $current->update(['sort_order' => $targetOrder]);
        $target->update(['sort_order' => $currentOrder]);
    }

    public function render()
    {
        return view('livewire.website.navbar-items-index', [
            'items' => NavbarItem::query()->orderBy('sort_order')->get(),
            'pages' => Page::query()->orderBy('sort_order')->get(),
            'systemLinks' => NavbarItem::systemLinkOptions(),
        ]);
    }
}
