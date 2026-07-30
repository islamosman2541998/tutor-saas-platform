<?php

namespace App\Actions\AcademicYears;

use App\Models\AcademicYear;
use App\Support\Activity\TenantActivity;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * Closing the tenant's *current* year also clears is_current /
 * tenant.current_academic_year_id — leaving a closed year marked "current"
 * would silently let new groups/enrollments attach to a year nobody is
 * teaching anymore. The teacher must explicitly pick a new current year
 * afterwards.
 */
class CloseAcademicYearAction
{
    public function __construct(protected TenantContext $context) {}

    public function execute(AcademicYear $year): AcademicYear
    {
        DB::transaction(function () use ($year) {
            $wasCurrent = $year->is_current;

            $year->update(['status' => 'closed', 'is_current' => false]);

            if ($wasCurrent) {
                $this->context->get()?->forceFill(['current_academic_year_id' => null])->save();
            }
        });

        TenantActivity::log('إغلاق العام الدراسي', $year, ['name' => $year->name]);

        return $year->fresh();
    }
}
