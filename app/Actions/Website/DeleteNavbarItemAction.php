<?php

namespace App\Actions\Website;

use App\Models\NavbarItem;
use App\Support\Activity\TenantActivity;

class DeleteNavbarItemAction
{
    public function execute(NavbarItem $item): void
    {
        $item->delete();

        TenantActivity::log('حذف عنصر شريط تنقّل', $item, ['label' => $item->label]);
    }
}
