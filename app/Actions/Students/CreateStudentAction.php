<?php

namespace App\Actions\Students;

use App\Models\Student;
use App\Support\Activity\TenantActivity;
use App\Support\Files\TenantUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateStudentAction
{
    public function __construct(protected TenantUploads $uploads) {}

    public function execute(array $data, ?UploadedFile $image = null): Student
    {
        return DB::transaction(function () use ($data, $image) {
            if ($image) {
                $data['image'] = $this->uploads->store($image, 'students');
            }

            $data['joined_at'] = $data['joined_at'] ?? now()->toDateString();
            $data['status'] = $data['status'] ?? 'active';

            // student_code is deliberately excluded from Student's fillable
            // list (never settable from a form) — forceCreate is required
            // here since this is the one legitimate place it's set.
            $student = Student::query()->forceCreate($data + [
                'student_code' => $this->generateCode(),
                'created_by' => Auth::id(),
            ]);

            TenantActivity::log('إنشاء طالب', $student, ['name' => $student->name]);

            return $student;
        });
    }

    protected function generateCode(): string
    {
        $count = Student::withTrashed()->count() + 1;
        $code = 'STU-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);

        while (Student::withTrashed()->where('student_code', $code)->exists()) {
            $count++;
            $code = 'STU-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);
        }

        return $code;
    }
}
