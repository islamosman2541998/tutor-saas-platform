<?php

namespace App\Support\Validation;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that a foreign key value (stage_id, subject_id, location_id, ...)
 * refers to a row the CURRENT tenant actually owns — never trust an id
 * submitted from a form just because it "looks" like a valid id.
 *
 * Relies on the target model already using BelongsToTenant/TenantScope:
 * a plain ::query()->whereKey($value)->exists() is automatically scoped to
 * the current tenant, so a row belonging to another tenant is invisible to
 * this check exactly like it's invisible everywhere else. This is why it's
 * safer than Laravel's built-in `exists:table,id` rule, which queries the
 * table directly and does NOT respect Eloquent global scopes.
 */
class BelongsToCurrentTenant implements ValidationRule
{
    public function __construct(protected string $modelClass, protected string $label) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Livewire's update requests skip Laravel's ConvertEmptyStringsToNull
        // middleware, so an untouched optional field arrives as '' rather
        // than null. Let a separate 'required' rule catch genuinely missing
        // values — this rule only judges whether a *provided* id is real.
        if ($value === null || $value === '') {
            return;
        }

        if (! $this->modelClass::query()->whereKey($value)->exists()) {
            $fail("{$this->label} المحدد غير صالح.");
        }
    }
}
