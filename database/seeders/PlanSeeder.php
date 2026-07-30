<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'الأساسية',
                'slug' => 'basic',
                'monthly_price' => 150,
                'yearly_price' => 1500,
                'trial_days' => 14,
                'max_students' => 50,
                'max_groups' => 5,
                'max_users' => 2,
                'website_enabled' => true,
                'custom_domain_enabled' => false,
                'excel_export_enabled' => false,
                'advanced_reports_enabled' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'المتقدمة',
                'slug' => 'pro',
                'monthly_price' => 300,
                'yearly_price' => 3000,
                'trial_days' => 14,
                'max_students' => 250,
                'max_groups' => 25,
                'max_users' => 5,
                'website_enabled' => true,
                'custom_domain_enabled' => false,
                'excel_export_enabled' => true,
                'advanced_reports_enabled' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'غير محدودة',
                'slug' => 'unlimited',
                'monthly_price' => 600,
                'yearly_price' => 6000,
                'trial_days' => 14,
                'max_students' => null,
                'max_groups' => null,
                'max_users' => null,
                'website_enabled' => true,
                'custom_domain_enabled' => true,
                'excel_export_enabled' => true,
                'advanced_reports_enabled' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->firstOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
