<?php

namespace App\Console\Commands;

use App\Actions\Billing\GenerateMonthlyDuesAction;
use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('dues:generate {--month=} {--year=}')]
#[Description('Generate this month\'s (or a specified month\'s) monthly dues for every active tenant.')]
class GenerateMonthlyDuesCommand extends Command
{
    public function handle(TenantContext $context, GenerateMonthlyDuesAction $action): int
    {
        $month = (int) ($this->option('month') ?? now()->month);
        $year = (int) ($this->option('year') ?? now()->year);

        $this->info("Generating monthly dues for {$month}/{$year}...");

        foreach (Tenant::query()->where('status', 'active')->get() as $tenant) {
            $context->set($tenant);

            $result = $action->execute($month, $year, generatedAutomatically: true, generatedBy: null);

            $this->line("  {$tenant->name}: {$result['created']} created, {$result['skipped']} already existed.");
        }

        $context->set(null);

        return self::SUCCESS;
    }
}
