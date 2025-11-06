<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{RecurringInvoice, RecurringInvoiceItem, Client, User};

class RecurringInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $client = Client::where('user_id',$user->id)->first();

        if (!$user || !$client) return;

        $tpl = RecurringInvoice::create([
            'user_id'      => $user->id,
            'client_id'    => $client->id,
            'start_date'   => now()->toDateString(),
            'frequency'    => 'monthly',
            'currency'     => 'EUR',
            'next_run_date'=> now()->toDateString(),
            'status'       => 'active',
            'public_notes' => 'Suscripción mensual',
            'terms'        => 'Pago a 15 días',
        ]);

        RecurringInvoiceItem::create([
            'recurring_invoice_id' => $tpl->id,
            'description' => 'Servicio mensual',
            'quantity'    => 1,
            'unit_price'  => 49.99,
            'tax_rate'    => 21,
            'discount'    => 0,
            'position'    => 0,
        ]);
    }
}
