<?php

namespace App\Actions\AcademicStructure;

use App\Exceptions\CannotDeleteException;
use App\Models\Location;
use App\Support\Activity\TenantActivity;

class DeleteLocationAction
{
    public function execute(Location $location): void
    {
        if ($location->groups()->exists()) {
            throw new CannotDeleteException(
                'لا يمكن حذف هذا المكان لأنه مستخدم في مجموعات قائمة. عطّل المكان بدلًا من حذفه.'
            );
        }

        $location->delete();

        TenantActivity::log('حذف مكان تدريس', $location, ['name' => $location->name]);
    }
}
