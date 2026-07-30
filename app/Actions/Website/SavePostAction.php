<?php

namespace App\Actions\Website;

use App\Models\Post;
use App\Support\Activity\TenantActivity;
use App\Support\Files\TenantUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SavePostAction
{
    public function __construct(protected TenantUploads $uploads) {}

    public function execute(array $data, ?Post $post = null, ?UploadedFile $image = null): Post
    {
        if ($image) {
            $data['image'] = $post && $post->image
                ? $this->uploads->replace($post->image, $image, 'posts')
                : $this->uploads->store($image, 'posts');
        }

        if ($data['status'] === 'published' && empty($data['published_at']) && ! ($post && $post->published_at)) {
            $data['published_at'] = now();
        }

        if ($post) {
            $post->update($data);
            TenantActivity::log('تعديل مقال/نصيحة', $post, ['title' => $post->title]);

            return $post->fresh();
        }

        // slug and author_id are deliberately excluded from Post's fillable
        // list — forceCreate is required here since this is the one
        // legitimate place they're set.
        $post = Post::query()->forceCreate($data + [
            'slug' => $this->uniqueSlug($data['title']),
            'author_id' => Auth::id(),
        ]);

        TenantActivity::log('إنشاء مقال/نصيحة', $post, ['title' => $post->title]);

        return $post;
    }

    protected function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        $suffix = 1;

        while (Post::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
