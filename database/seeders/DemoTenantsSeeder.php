<?php

namespace Database\Seeders;

use App\Actions\Tenancy\AssignTenantSubscriptionAction;
use App\Actions\Tenancy\RegisterTenantAction;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Two demo tenants used to manually verify tenant isolation: logging in as
 * one must never expose the other's data.
 */
class DemoTenantsSeeder extends Seeder
{
    public function run(RegisterTenantAction $registerTenant, AssignTenantSubscriptionAction $assignSubscription): void
    {
        if (User::query()->where('email', 'ahmed@tutor-saas.test')->exists()) {
            return;
        }

        $proPlan = Plan::query()->where('slug', 'pro')->first();

        $ahmed = $registerTenant->execute([
            'teacher_name' => 'أحمد سمير',
            'activity_name' => 'مركز أحمد للرياضيات',
            'email' => 'ahmed@tutor-saas.test',
            'phone' => '01000000001',
            'password' => 'password',
        ]);

        $sara = $registerTenant->execute([
            'teacher_name' => 'سارة علي',
            'activity_name' => 'مركز سارة للغة الإنجليزية',
            'email' => 'sara@tutor-saas.test',
            'phone' => '01000000002',
            'password' => 'password',
        ]);

        if ($proPlan) {
            foreach ([$ahmed, $sara] as $tenant) {
                $assignSubscription->execute($tenant, [
                    'plan_id' => $proPlan->id,
                    'billing_cycle' => 'monthly',
                    'status' => 'active',
                    'starts_at' => now()->toDateString(),
                    'amount' => $proPlan->monthly_price,
                ]);
            }
        }
    }
}
