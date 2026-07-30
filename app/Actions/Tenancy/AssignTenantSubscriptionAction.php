<?php

namespace App\Actions\Tenancy;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Records a new subscription period for a tenant (initial signup, plan
 * change, renewal, manual extension by Super Admin). Past subscription rows
 * are never edited in place — each assignment is a new row, so the tenant's
 * billing history stays intact.
 */
class AssignTenantSubscriptionAction
{
    public function execute(Tenant $tenant, array $data): TenantSubscription
    {
        return DB::transaction(function () use ($tenant, $data) {
            $plan = Plan::query()->findOrFail($data['plan_id']);

            $subscription = TenantSubscription::query()->create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'] ?? null,
                'trial_ends_at' => $data['trial_ends_at'] ?? null,
                'billing_cycle' => $data['billing_cycle'],
                'status' => $data['status'],
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            TenantActivity::log('تغيير باقة اشتراك المدرس', $tenant, [
                'plan' => $plan->name,
                'status' => $subscription->status,
                'ends_at' => $subscription->ends_at?->toDateString(),
            ]);

            return $subscription;
        });
    }
}
