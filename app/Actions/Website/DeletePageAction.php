<?php

namespace App\Actions\Website;

use App\Models\Page;
use App\Support\Activity\TenantActivity;

class DeletePageAction
{
    public function execute(Page $page): void
    {
        $page->delete();

        TenantActivity::log('حذف صفحة', $page, ['title' => $page->title]);
    }
}
