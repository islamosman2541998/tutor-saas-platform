<?php

namespace App\Actions\AcademicStructure;

use App\Exceptions\CannotDeleteException;
use App\Models\Stage;
use App\Support\Activity\TenantActivity;

class DeleteStageAction
{
    public function execute(Stage $stage): void
    {
        if ($stage->grades()->exists()) {
            throw new CannotDeleteException(
                'لا يمكن حذف هذه المرحلة لأنها تحتوي على صفوف دراسية. احذف الصفوف أولًا أو عطّل المرحلة بدلًا من حذفها.'
            );
        }

        if ($stage->groups()->exists()) {
            throw new CannotDeleteException(
                'لا يمكن حذف هذه المرحلة لأنها مستخدمة في مجموعات قائمة. عطّل المرحلة بدلًا من حذفها.'
            );
        }

        $stage->delete();

        TenantActivity::log('حذف مرحلة تعليمية', $stage, ['name' => $stage->name]);
    }
}
