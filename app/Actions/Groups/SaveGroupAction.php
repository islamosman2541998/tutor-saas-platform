<?php

namespace App\Actions\Groups;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Location;
use App\Models\Stage;
use App\Models\Subject;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Creates or updates a Group. Even though the Livewire form already
 * validates each foreign key with BelongsToCurrentTenant, this Action
 * re-verifies ownership and the stage/grade relationship itself before
 * writing — Actions must stay safe to call from anywhere (a future import
 * job, an API endpoint, ...), not just from the one form that happens to
 * validate correctly today.
 */
class SaveGroupAction
{
    public function execute(array $data, ?Group $group = null): Group
    {
        $this->assertOwnedByTenant(AcademicYear::class, $data['academic_year_id'], 'العام الدراسي');
        $this->assertOwnedByTenant(Stage::class, $data['stage_id'], 'المرحلة');
        $this->assertOwnedByTenant(Subject::class, $data['subject_id'], 'المادة');
        $this->assertOwnedByTenant(Location::class, $data['location_id'], 'المكان');

        /** @var Grade $grade */
        $grade = $this->assertOwnedByTenant(Grade::class, $data['grade_id'], 'الصف');

        if ($grade->stage_id !== (int) $data['stage_id']) {
            throw ValidationException::withMessages([
                'grade_id' => 'الصف المحدد لا ينتمي للمرحلة المختارة.',
            ]);
        }

        if ($group) {
            $group->update($data);
            TenantActivity::log('تعديل مجموعة', $group, ['name' => $group->name]);

            return $group->fresh();
        }

        // code / created_by are deliberately excluded from Group's fillable
        // list (never settable from a form) — forceCreate is required here
        // since this is the one legitimate place they're allowed to be set.
        $group = Group::query()->forceCreate($data + [
            'code' => $this->uniqueCode($data['name']),
            'created_by' => Auth::id(),
        ]);

        TenantActivity::log('إنشاء مجموعة', $group, ['name' => $group->name]);

        return $group;
    }

    protected function assertOwnedByTenant(string $modelClass, mixed $id, string $label): mixed
    {
        $model = $modelClass::query()->find($id);

        if (! $model) {
            throw ValidationException::withMessages([
                'name' => "{$label} المحدد غير صالح.",
            ]);
        }

        return $model;
    }

    protected function uniqueCode(string $name): string
    {
        $base = Str::upper(Str::slug($name, '')) ?: 'GRP';
        $base = Str::substr($base, 0, 10);
        $code = $base.'-'.random_int(100, 999);

        while (Group::withTrashed()->where('code', $code)->exists()) {
            $code = $base.'-'.random_int(100, 999);
        }

        return $code;
    }
}
