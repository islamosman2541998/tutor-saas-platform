<?php

namespace App\Actions\AcademicStructure;

use App\Exceptions\CannotDeleteException;
use App\Models\Grade;
use App\Support\Activity\TenantActivity;

class DeleteGradeAction
{
    public function execute(Grade $grade): void
    {
        if ($grade->groups()->exists()) {
            throw new CannotDeleteException(
                'لا يمكن حذف هذا الصف لأنه مستخدم في مجموعات قائمة. عطّل الصف بدلًا من حذفه.'
            );
        }

        $grade->delete();

        TenantActivity::log('حذف صف دراسي', $grade, ['name' => $grade->name]);
    }
}
