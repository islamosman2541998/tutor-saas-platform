<?php

namespace App\Actions\Billing;

use App\Exceptions\CannotPerformActionException;
use App\Models\MonthlyDue;
use App\Support\Activity\TenantActivity;

/**
 * Waiving or cancelling a due never deletes it — the record stays as
 * evidence of what was owed and why it was written off.
 */
class SetMonthlyDueStatusAction
{
    public function execute(MonthlyDue $due, string $status, ?string $reason = null): MonthlyDue
    {
        if (in_array($due->status, ['paid', 'cancelled', 'waived'], true)) {
            throw new CannotPerformActionException('لا يمكن تعديل استحقاق تمت تسويته بالفعل (مدفوع أو مُعفى أو مُلغى).');
        }

        if ($due->paid_amount > 0) {
            throw new CannotPerformActionException('لا يمكن إعفاء أو إلغاء استحقاق له دفعات مسجَّلة. راجع الدفعات أولًا.');
        }

        $due->update([
            'status' => $status,
            'waived_reason' => $reason,
            'remaining_amount' => 0,
        ]);

        TenantActivity::log(
            $status === 'waived' ? 'إعفاء استحقاق شهري' : 'إلغاء استحقاق شهري',
            $due,
            ['reason' => $reason],
        );

        return $due->fresh();
    }
}
