<?php

namespace App\Actions\Website;

use App\Models\Page;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Str;

class SavePageAction
{
    public function execute(array $data, ?Page $page = null): Page
    {
        if ($page) {
            $page->update($data);
            TenantActivity::log('تعديل صفحة', $page, ['title' => $page->title]);

            return $page->fresh();
        }

        // slug is deliberately excluded from Page's fillable list —
        // forceCreate is required here since this is the one legitimate
        // place it's set.
        $page = Page::query()->forceCreate($data + [
            'slug' => $this->uniqueSlug($data['title']),
        ]);

        TenantActivity::log('إنشاء صفحة', $page, ['title' => $page->title]);

        return $page;
    }

    protected function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'page';
        $slug = $base;
        $suffix = 1;

        while (Page::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
