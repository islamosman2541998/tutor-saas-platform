<?php

namespace App\Actions\AcademicYears;

use App\Models\AcademicYear;
use App\Support\Activity\TenantActivity;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * Enforces "at most one current academic year per tenant" — the invariant
 * can't be a DB constraint alone (history of past "current" years must
 * survive), so it's enforced here inside a transaction: clear every other
 * year first, then set the target one.
 */
class SetCurrentAcademicYearAction
{
    public function __construct(protected TenantContext $context) {}

    public function execute(AcademicYear $year): AcademicYear
    {
        DB::transaction(function () use ($year) {
            AcademicYear::query()
                ->where('id', '!=', $year->id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            $year->update(['is_current' => true]);

            // current_academic_year_id is deliberately absent from Tenant's
            // fillable list (it must never be settable from a form) —
            // forceFill is required here since this is the one legitimate
            // place it's allowed to change.
            $this->context->get()?->forceFill(['current_academic_year_id' => $year->id])->save();
        });

        TenantActivity::log('تحديد العام الدراسي الحالي', $year, ['name' => $year->name]);

        return $year->fresh();
    }
}
