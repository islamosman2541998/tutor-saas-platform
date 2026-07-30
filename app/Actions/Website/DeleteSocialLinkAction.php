<?php

namespace App\Actions\Website;

use App\Models\SocialLink;
use App\Support\Activity\TenantActivity;

class DeleteSocialLinkAction
{
    public function execute(SocialLink $link): void
    {
        $link->delete();

        TenantActivity::log('حذف رابط تواصل اجتماعي', $link, ['platform' => $link->platform]);
    }
}
