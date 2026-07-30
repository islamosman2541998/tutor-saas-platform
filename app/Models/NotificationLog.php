<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'tenant_id', 'notification_type', 'channel', 'recipient', 'status',
        'sent_at', 'failed_at', 'error_message', 'related_model_type', 'related_model_id',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo('related', 'related_model_type', 'related_model_id');
    }
}
