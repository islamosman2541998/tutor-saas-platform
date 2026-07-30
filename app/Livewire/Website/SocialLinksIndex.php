<?php

namespace App\Livewire\Website;

use App\Actions\Website\DeleteSocialLinkAction;
use App\Livewire\Concerns\Notifies;
use App\Models\SocialLink;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SocialLinksIndex extends Component
{
    use Notifies;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $platform = 'facebook';

    public string $url = '';

    public string $display_location = 'all';

    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('website.manage');
    }

    public function create(): void
    {
        $this->authorize('website.manage');

        $this->reset(['editingId', 'url']);
        $this->platform = 'facebook';
        $this->display_location = 'all';
        $this->is_active = true;
        $this->showForm = true;
    }

    public function edit(int $linkId): void
    {
        $this->authorize('website.manage');

        $link = SocialLink::query()->findOrFail($linkId);
        $this->editingId = $link->id;
        $this->platform = $link->platform;
        $this->url = $link->url;
        $this->display_location = $link->display_location;
        $this->is_active = $link->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize('website.manage');

        $data = $this->validate([
            'platform' => ['required', 'in:facebook,instagram,tiktok,youtube,whatsapp,telegram,linkedin,x,other'],
            'url' => ['required', 'url', 'max:255'],
            'display_location' => ['required', 'in:navbar,footer,contact,all'],
        ], [], [
            'url' => 'الرابط',
        ]);

        $data['is_active'] = $this->is_active;

        if ($this->editingId) {
            SocialLink::query()->findOrFail($this->editingId)->update($data);
            $this->toast('تم تحديث رابط التواصل.');
        } else {
            $data['sort_order'] = (SocialLink::query()->max('sort_order') ?? 0) + 1;
            SocialLink::query()->create($data);
            $this->toast('تم إضافة رابط التواصل.');
        }

        $this->showForm = false;
    }

    public function askDelete(int $linkId): void
    {
        $link = SocialLink::query()->findOrFail($linkId);

        $this->confirmThen(
            confirmEvent: 'social-link-delete-confirmed',
            title: 'حذف رابط التواصل؟',
            text: "سيتم حذف رابط \"{$link->platformLabel()}\" نهائيًا.",
            params: ['linkId' => $linkId],
            confirmButtonText: 'حذف',
        );
    }

    #[On('social-link-delete-confirmed')]
    public function delete(int $linkId, DeleteSocialLinkAction $action): void
    {
        $this->authorize('website.manage');

        $action->execute(SocialLink::query()->findOrFail($linkId));
        $this->toast('تم حذف رابط التواصل.');
    }

    public function moveUp(int $linkId): void
    {
        $this->swapSortOrder($linkId, 'up');
    }

    public function moveDown(int $linkId): void
    {
        $this->swapSortOrder($linkId, 'down');
    }

    protected function swapSortOrder(int $linkId, string $direction): void
    {
        $this->authorize('website.manage');

        $links = SocialLink::query()->orderBy('sort_order')->get();
        $index = $links->search(fn ($l) => $l->id === $linkId);

        $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || ! $links->has($targetIndex)) {
            return;
        }

        $current = $links[$index];
        $target = $links[$targetIndex];

        [$currentOrder, $targetOrder] = [$current->sort_order, $target->sort_order];
        $current->update(['sort_order' => $targetOrder]);
        $target->update(['sort_order' => $currentOrder]);
    }

    public function render()
    {
        return view('livewire.website.social-links-index', [
            'links' => SocialLink::query()->orderBy('sort_order')->get(),
        ]);
    }
}
