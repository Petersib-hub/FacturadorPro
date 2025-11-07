<?php

namespace App\Support\Verifactu;

use App\Models\Verifactu\EventLog;
use App\Enums\Verifactu\LogEventType;
use Illuminate\Http\Request;

class EventLogger
{
    public static function log(string $eventType, array $context = [], ?Request $request = null, $tenant = null, $actor = null): void
    {
        EventLog::create([
            'tenant_type' => $tenant ? get_class($tenant) : null,
            'tenant_id' => $tenant->id ?? null,
            'actor_type' => $actor ? get_class($actor) : null,
            'actor_id' => $actor->id ?? null,
            'event_type' => $eventType,
            'source' => $request ? ($request->header('X-Source') ?? 'ui') : 'system',
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'context' => $context,
        ]);
    }
}
