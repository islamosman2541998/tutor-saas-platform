<?php

namespace App\Actions\Website;

use App\Models\Post;
use App\Support\Activity\TenantActivity;

class DeletePostAction
{
    public function execute(Post $post): void
    {
        $post->delete();

        TenantActivity::log('حذف مقال/نصيحة', $post, ['title' => $post->title]);
    }
}
