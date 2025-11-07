<?php

namespace App\Jobs\Verifactu;

use App\Services\Fiskaly\FiskalyClient;
use App\Support\Verifactu\EventLogger;
use App\Enums\Verifactu\LogEventType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateOnboardingEntities implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $data, public $tenant = null) {}

    public function handle(FiskalyClient $client): void
    {
        $org = $client->ensureOrganization($this->data['organization'] ?? []);
        $tax = $client->ensureTaxpayer($this->data['taxpayer'] ?? []);
        $sig = $client->ensureSigner($this->data['signer'] ?? []);
        $cli = $client->ensureClient($this->data['client'] ?? []);

        EventLogger::log(LogEventType::ONBOARDING_COMPLETED->value, compact('org','tax','sig','cli'), null, $this->tenant);
    }
}
