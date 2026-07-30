<?php

namespace App\Actions\Website;

use App\Models\Tenant;
use App\Support\Activity\TenantActivity;

class SetWebsiteStatusAction
{
    public function execute(Tenant $tenant, string $status): Tenant
    {
        $previous = $tenant->website_status;

        $tenant->update(['website_status' => $status]);

        if ($previous !== $status) {
            $labels = ['draft' => 'مسودة', 'published' => 'منشور', 'maintenance' => 'تحت الصيانة'];
            TenantActivity::log('تغيير حالة نشر الموقع التعريفي', $tenant, [
                'from' => $labels[$previous] ?? $previous,
                'to' => $labels[$status] ?? $status,
            ]);
        }

        return $tenant->fresh();
    }
}
