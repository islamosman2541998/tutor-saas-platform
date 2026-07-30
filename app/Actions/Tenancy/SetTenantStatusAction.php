<?php

namespace App\Actions\Tenancy;

use App\Models\Tenant;
use App\Support\Activity\TenantActivity;

class SetTenantStatusAction
{
    public function execute(Tenant $tenant, string $status): Tenant
    {
        $previous = $tenant->status;

        $tenant->update(['status' => $status]);

        if ($previous !== $status) {
            TenantActivity::log(self::label($status), $tenant, ['from' => $previous, 'to' => $status]);
        }

        return $tenant;
    }

    protected static function label(string $status): string
    {
        return match ($status) {
            'active' => 'تفعيل حساب المدرس',
            'suspended' => 'إيقاف حساب المدرس',
            'rejected' => 'رفض طلب التسجيل',
            default => 'تحديث حالة الحساب',
        };
    }
}
