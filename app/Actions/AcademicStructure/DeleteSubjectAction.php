<?php

namespace App\Actions\AcademicStructure;

use App\Exceptions\CannotDeleteException;
use App\Models\Subject;
use App\Support\Activity\TenantActivity;
use App\Support\Files\TenantUploads;

class DeleteSubjectAction
{
    public function __construct(protected TenantUploads $uploads) {}

    public function execute(Subject $subject): void
    {
        if ($subject->groups()->exists()) {
            throw new CannotDeleteException(
                'لا يمكن حذف هذه المادة لأنها مستخدمة في مجموعات قائمة. عطّل المادة بدلًا من حذفها.'
            );
        }

        $this->uploads->delete($subject->image);

        $subject->delete();

        TenantActivity::log('حذف مادة دراسية', $subject, ['name' => $subject->name]);
    }
}
