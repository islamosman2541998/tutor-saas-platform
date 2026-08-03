<?php

namespace App\Console\Commands;

use App\Models\DashboardAppearanceSettings;
use App\Models\LoginPageSettings;
use App\Models\Tenant;
use App\Models\WebsiteSettings;
use App\Support\Tenancy\TenantContext;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('settings:backfill {--tenant= : Limit to a single tenant by id or slug}')]
#[Description('Create any missing per-tenant settings rows (login page, dashboard appearance, website) using their defaults. Run once after deploying a change that adds a new settings table/column, so existing tenants who never opened that settings page get a row too.')]
class BackfillTenantSettingsCommand extends Command
{
    public function handle(TenantContext $context): int
    {
        $tenantOption = $this->option('tenant');

        $tenants = Tenant::query()
            ->when($tenantOption, fn ($query) => $query->where(
                fn ($q) => $q->where('id', $tenantOption)->orWhere('slug', $tenantOption)
            ))
            ->get();

        if ($tenants->isEmpty()) {
            $this->error('No matching tenant found.');

            return self::FAILURE;
        }

        $this->info("Checking settings for {$tenants->count()} tenant(s)...");

        foreach ($tenants as $tenant) {
            $context->set($tenant);

            $created = [];

            if (LoginPageSettings::query()->first() === null) {
                LoginPageSettings::defaults()->save();
                $created[] = 'login page';
            }

            if (DashboardAppearanceSettings::query()->first() === null) {
                DashboardAppearanceSettings::defaults()->save();
                $created[] = 'dashboard appearance';
            }

            if (WebsiteSettings::query()->first() === null) {
                (new WebsiteSettings)->save();
                $created[] = 'website';
            }

            $this->line($created === []
                ? "  {$tenant->name}: already complete."
                : "  {$tenant->name}: created ".implode(', ', $created).'.');
        }

        $context->set(null);

        $this->info('Done.');

        return self::SUCCESS;
    }
}
