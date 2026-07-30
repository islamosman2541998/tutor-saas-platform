<?php

namespace App\Livewire\Website;

use App\Actions\Website\DeletePageAction;
use App\Actions\Website\SavePageAction;
use App\Livewire\Concerns\Notifies;
use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PagesIndex extends Component
{
    use Notifies, WithPagination;

    public string $search = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $content = '';

    public string $status = 'draft';

    public bool $show_in_navbar = false;

    public bool $show_in_footer = false;

    public string $meta_title = '';

    public string $meta_description = '';

    public function mount(): void
    {
        $this->authorize('website.manage');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorize('website.manage');

        $this->reset(['editingId', 'title', 'content', 'show_in_navbar', 'show_in_footer', 'meta_title', 'meta_description']);
        $this->status = 'draft';
        $this->showForm = true;
    }

    public function edit(int $pageId): void
    {
        $this->authorize('website.manage');

        $page = Page::query()->findOrFail($pageId);
        $this->editingId = $page->id;
        $this->title = $page->title;
        $this->content = $page->content;
        $this->status = $page->status;
        $this->show_in_navbar = $page->show_in_navbar;
        $this->show_in_footer = $page->show_in_footer;
        $this->meta_title = (string) $page->meta_title;
        $this->meta_description = (string) $page->meta_description;
        $this->showForm = true;
    }

    public function save(SavePageAction $action): void
    {
        $this->authorize('website.manage');

        $page = $this->editingId ? Page::query()->findOrFail($this->editingId) : null;

        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
        ], [], [
            'title' => 'العنوان',
            'content' => 'المحتوى',
        ]);

        $data['show_in_navbar'] = $this->show_in_navbar;
        $data['show_in_footer'] = $this->show_in_footer;
        $data['meta_title'] = $data['meta_title'] ?: null;
        $data['meta_description'] = $data['meta_description'] ?: null;

        $action->execute($data, $page);

        $this->toast($page ? 'تم تحديث الصفحة.' : 'تم إنشاء الصفحة.');
        $this->showForm = false;
    }

    public function askDelete(int $pageId): void
    {
        $page = Page::query()->findOrFail($pageId);

        $this->confirmThen(
            confirmEvent: 'page-delete-confirmed',
            title: 'حذف الصفحة؟',
            text: "سيتم حذف \"{$page->title}\" نهائيًا.",
            params: ['pageId' => $pageId],
            confirmButtonText: 'حذف',
        );
    }

    #[On('page-delete-confirmed')]
    public function delete(int $pageId, DeletePageAction $action): void
    {
        $this->authorize('website.manage');

        $action->execute(Page::query()->findOrFail($pageId));
        $this->toast('تم حذف الصفحة.');
    }

    public function render()
    {
        $pages = Page::query()
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.website.pages-index', ['pages' => $pages]);
    }
}
