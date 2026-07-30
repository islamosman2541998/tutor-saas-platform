<?php

namespace App\Actions\Tenancy;

use App\Models\SubscriptionPayment;
use App\Models\TenantSubscription;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Facades\Auth;

class RecordSubscriptionPaymentAction
{
    public function execute(TenantSubscription $subscription, array $data): SubscriptionPayment
    {
        $payment = SubscriptionPayment::query()->create([
            'tenant_subscription_id' => $subscription->id,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'paid_at' => $data['paid_at'],
            'reference_number' => $data['reference_number'] ?? null,
            'notes' => $data['notes'] ?? null,
            'recorded_by' => Auth::id(),
        ]);

        TenantActivity::log('تسجيل دفعة اشتراك', $subscription->tenant, [
            'plan' => $subscription->plan->name,
            'amount' => (string) $payment->amount,
            'payment_method' => $payment->payment_method,
        ]);

        return $payment;
    }
}
