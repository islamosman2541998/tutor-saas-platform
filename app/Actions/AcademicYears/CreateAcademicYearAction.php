<?php

namespace App\Actions\AcademicYears;

use App\Models\AcademicYear;
use App\Support\Activity\TenantActivity;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateAcademicYearAction
{
    public function __construct(
        protected TenantContext $context,
        protected SetCurrentAcademicYearAction $setCurrent,
    ) {}

    public function execute(array $data): AcademicYear
    {
        return DB::transaction(function () use ($data) {
            $isFirstYear = ! AcademicYear::query()->exists();

            $year = AcademicYear::query()->create([
                'name' => $data['name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            TenantActivity::log('إنشاء عام دراسي', $year, ['name' => $year->name]);

            if ($isFirstYear || ! empty($data['make_current'])) {
                $this->setCurrent->execute($year);
            }

            return $year->fresh();
        });
    }
}
