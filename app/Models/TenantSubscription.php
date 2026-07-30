<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Deliberately NOT tenant-scoped (no BelongsToTenant/TenantScope): this is a
 * Super Admin-managed resource that must stay visible across every tenant
 * regardless of the current TenantContext, which is exactly the opposite of
 * what TenantScope's safe-default (return nothing outside a tenant request)
 * would allow.
 */
#[Fillable([
    'tenant_id', 'plan_id', 'starts_at', 'ends_at', 'trial_ends_at',
    'billing_cycle', 'status', 'amount', 'notes', 'created_by',
])]
class TenantSubscription extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'trial_ends_at' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['trial', 'active'], true);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function remainingAmount(): float
    {
        return (float) $this->amount - $this->totalPaid();
    }
}
