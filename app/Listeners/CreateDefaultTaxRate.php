<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Models\TaxRate;

class CreateDefaultTaxRate
{
    public function handle(Registered $event): void
    {
        $user = $event->user;
        if (!TaxRate::where('user_id', $user->id)->exists()) {
            TaxRate::create([
                'user_id' => $user->id,
                'name' => 'IVA 21%',
                'rate' => 21.000,
                'is_default' => true,
            ]);
        }
    }
}
