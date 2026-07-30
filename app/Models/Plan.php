<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name', 'slug', 'description', 'monthly_price', 'yearly_price', 'trial_days',
    'max_students', 'max_groups', 'max_users', 'max_storage_mb',
    'website_enabled', 'custom_domain_enabled', 'excel_export_enabled',
    'email_notifications_enabled', 'advanced_reports_enabled',
    'is_active', 'sort_order',
])]
class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'website_enabled' => 'boolean',
            'custom_domain_enabled' => 'boolean',
            'excel_export_enabled' => 'boolean',
            'email_notifications_enabled' => 'boolean',
            'advanced_reports_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }
}
