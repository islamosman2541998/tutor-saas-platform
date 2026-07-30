<?php

namespace App\Livewire\Website;

use App\Actions\Website\DeletePostAction;
use App\Actions\Website\SavePostAction;
use App\Livewire\Concerns\Notifies;
use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PostsIndex extends Component
{
    use Notifies, WithFileUploads, WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $excerpt = '';

    public string $content = '';

    public string $status = 'draft';

    public string $published_at = '';

    public bool $is_featured = false;

    public string $meta_title = '';

    public string $meta_description = '';

    public $image = null;

    public ?string $existingImage = null;

    public function mount(): void
    {
        $this->authorize('website.manage');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorize('website.manage');

        $this->reset([
            'editingId', 'title', 'excerpt', 'content', 'published_at', 'is_featured',
            'meta_title', 'meta_description', 'image', 'existingImage',
        ]);
        $this->status = 'draft';
        $this->showForm = true;
    }

    public function edit(int $postId): void
    {
        $this->authorize('website.manage');

        $post = Post::query()->findOrFail($postId);
        $this->editingId = $post->id;
        $this->title = $post->title;
        $this->excerpt = (string) $post->excerpt;
        $this->content = $post->content;
        $this->status = $post->status;
        $this->published_at = optional($post->published_at)->format('Y-m-d\TH:i') ?? '';
        $this->is_featured = $post->is_featured;
        $this->meta_title = (string) $post->meta_title;
        $this->meta_description = (string) $post->meta_description;
        $this->existingImage = $post->image;
        $this->image = null;
        $this->showForm = true;
    }

    public function save(SavePostAction $action): void
    {
        $this->authorize('website.manage');

        $post = $this->editingId ? Post::query()->findOrFail($this->editingId) : null;

        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], [
            'title' => 'العنوان',
            'content' => 'المحتوى',
        ]);

        $image = $this->image;
        unset($data['image']);

        $data['is_featured'] = $this->is_featured;
        $data['published_at'] = $data['published_at'] ?: null;
        $data['excerpt'] = $data['excerpt'] ?: null;
        $data['meta_title'] = $data['meta_title'] ?: null;
        $data['meta_description'] = $data['meta_description'] ?: null;

        $action->execute($data, $post, $image);

        $this->toast($post ? 'تم تحديث المقال.' : 'تم إنشاء المقال.');
        $this->showForm = false;
    }

    public function askDelete(int $postId): void
    {
        $post = Post::query()->findOrFail($postId);

        $this->confirmThen(
            confirmEvent: 'post-delete-confirmed',
            title: 'حذف المقال؟',
            text: "سيتم حذف \"{$post->title}\" نهائيًا.",
            params: ['postId' => $postId],
            confirmButtonText: 'حذف',
        );
    }

    #[On('post-delete-confirmed')]
    public function delete(int $postId, DeletePostAction $action): void
    {
        $this->authorize('website.manage');

        $action->execute(Post::query()->findOrFail($postId));
        $this->toast('تم حذف المقال.');
    }

    public function render()
    {
        $posts = Post::query()
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.website.posts-index', ['posts' => $posts]);
    }
}
