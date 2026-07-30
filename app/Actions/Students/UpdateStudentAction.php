<?php

namespace App\Actions\Students;

use App\Models\Student;
use App\Support\Activity\TenantActivity;
use App\Support\Files\TenantUploads;
use Illuminate\Http\UploadedFile;

class UpdateStudentAction
{
    public function __construct(protected TenantUploads $uploads) {}

    public function execute(Student $student, array $data, ?UploadedFile $image = null): Student
    {
        if ($image) {
            $data['image'] = $this->uploads->replace($student->image, $image, 'students');
        }

        $student->update($data);

        TenantActivity::log('تعديل بيانات طالب', $student, ['name' => $student->name]);

        return $student->fresh();
    }
}
