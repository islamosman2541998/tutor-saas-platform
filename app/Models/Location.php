<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name', 'type', 'country', 'governorate', 'city', 'area', 'address',
    'google_maps_url', 'phone', 'notes', 'is_active',
])]
class Location extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'center' => 'سنتر',
            'home' => 'منزل',
            'online' => 'أونلاين',
            default => 'مكان آخر',
        };
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }
}
