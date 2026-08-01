<?php

namespace App\Console\Commands;

use App\Models\TenantSubscription;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * EnsureSubscriptionIsValid already blocks access the instant a
 * subscription's end date passes, regardless of its stored status — this
 * command just flips that status to 'expired' so the Super Admin's
 * subscriptions list (App\Livewire\Admin\SubscriptionsIndex) reports it
 * accurately instead of showing a lapsed subscription as still "active".
 */
#[Signature('subscriptions:expire')]
#[Description("Marks trial/active subscriptions whose end date has passed as 'expired'.")]
class ExpireTenantSubscriptionsCommand extends Command
{
    public function handle(): int
    {
        $expiredTrials = TenantSubscription::query()
            ->where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now()->toDateString())
            ->update(['status' => 'expired']);

        $expiredActive = TenantSubscription::query()
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now()->toDateString())
            ->update(['status' => 'expired']);

        $this->info("Expired {$expiredTrials} trial and {$expiredActive} active subscription(s).");

        return self::SUCCESS;
    }
}
