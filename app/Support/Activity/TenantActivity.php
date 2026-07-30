<?php

namespace App\Support\Activity;

use App\Models\Activity;
use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;

/**
 * Every sensitive action listed in the spec (payments, attendance edits,
 * settings changes, tenant status changes, Support Login, ...) should log
 * through here instead of calling activity() directly, so tenant_id /
 * ip_address / user_agent are never forgotten on any individual call site.
 */
class TenantActivity
{
    public static function log(string $description, ?Model $subject = null, array $properties = []): Activity
    {
        $logger = activity()->causedBy(auth()->user());

        if ($subject) {
            $logger->performedOn($subject);
        }

        if ($properties !== []) {
            $logger->withProperties($properties);
        }

        return $logger->tap(function (Activity $activity) use ($subject) {
            $activity->tenant_id = self::resolveTenantId($subject);
            $activity->ip_address = request()->ip();
            $activity->user_agent = (string) request()->userAgent();
        })->log($description);
    }

    /**
     * Prefer the subject's own tenant over the acting TenantContext: a
     * Super Admin suspending tenant X is logged against tenant X, not
     * against the (tenant-less) central context they're acting from.
     */
    protected static function resolveTenantId(?Model $subject): ?int
    {
        if ($subject instanceof Tenant) {
            return $subject->id;
        }

        if ($subject && isset($subject->tenant_id)) {
            return $subject->tenant_id;
        }

        return app(TenantContext::class)->id();
    }
}
