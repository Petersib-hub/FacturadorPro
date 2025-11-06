<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\NumberSequence;
use App\Models\Product;
use App\Models\TaxRate;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // === Usuario demo (o toma el primero existente) ===
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name'              => 'Demo User',
                'email'             => 'demo@example.com',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'remember_token'    => Str::random(10),
            ]);
            $this->command->info('Usuario demo creado: demo@example.com / password');
        } else {
            $this->command->info('Usando usuario existente: ' . $user->email);
        }

        // === Ajustes del tenant ===
        UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'legal_name'    => 'Mi Empresa Demo S.L.',
                'tax_id'        => 'B12345678',
                'address'       => 'Calle Ejemplo 123',
                'zip'           => '28001',
                'city'          => 'Madrid',
                'country'       => 'ES',
                'currency_code' => 'EUR',
                'locale'        => 'es_ES',
                'timezone'      => 'Europe/Madrid',
                'pdf_template'  => 'classic', // classic|modern|minimal
                // 'logo_path'   => 'logos/mi_logo.png', // si quisieras forzar un logo ya subido
            ]
        );

        // === Tasa por defecto ===
        $tax = TaxRate::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'IVA 21'],
            ['rate' => 21, 'is_default' => true, 'is_exempt' => false]
        );

        // === Producto demo ===
        $product = Product::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Servicio demo'],
            ['unit_price' => 100] // <- en lugar de 'price' => 100
        );

        // === Cliente demo ===
        $client = Client::firstOrCreate(
            ['user_id' => $user->id, 'email' => 'cliente.demo@example.com'],
            [
                'name'         => 'Cliente Demo',
                'city'         => 'Madrid',
                'public_token' => Str::random(48),
            ]
        );

        $year = now()->year;

        // ====== Sincroniza numeración de budgets ======
        $nsBudget = NumberSequence::firstOrCreate(
            ['user_id' => $user->id, 'type' => 'budget', 'year' => $year],
            ['last' => 0]
        );
        $maxBudgetSeq = (int) Budget::where('user_id', $user->id)->where('year', $year)->max('sequence');
        if ($nsBudget->last < $maxBudgetSeq) {
            $nsBudget->last = $maxBudgetSeq;
            $nsBudget->save();
        }

        // ====== Sincroniza numeración de invoices ======
        $nsInvoice = NumberSequence::firstOrCreate(
            ['user_id' => $user->id, 'type' => 'invoice', 'year' => $year],
            ['last' => 0]
        );
        $maxInvoiceSeq = (int) Invoice::where('user_id', $user->id)->where('year', $year)->max('sequence');
        if ($nsInvoice->last < $maxInvoiceSeq) {
            $nsInvoice->last = $maxInvoiceSeq;
            $nsInvoice->save();
        }

        // ====== Crea un presupuesto con items ======
        $budgetNumber = NumberSequence::next('budget', $user->id); // p.ej. PRES-2025-0001
        preg_match('/^PRES-(\d{4})-(\d{4})$/', $budgetNumber, $mb);
        $budgetYear = (int)($mb[1] ?? $year);
        $budgetSeq  = (int)($mb[2] ?? 1);

        $budget = Budget::create([
            'user_id'      => $user->id,
            'client_id'    => $client->id,
            'number'       => $budgetNumber,
            'sequence'     => $budgetSeq,
            'year'         => $budgetYear,
            'date'         => now()->toDateString(),
            'due_date'     => now()->addDays(15)->toDateString(),
            'currency'     => 'EUR',
            'status'       => 'draft',
            'notes'        => 'Notas demo del presupuesto.',
            'terms'        => 'Pago por transferencia en 30 días.',
            'public_token' => Str::random(48),
        ]);

        // Items del presupuesto
        $items = [
            ['description' => 'Servicio demo', 'quantity' => 2, 'unit_price' => 100.00, 'tax_rate' => $tax->rate, 'discount' => 0],
            ['description' => 'Consultoría adicional', 'quantity' => 1, 'unit_price' => 50.00, 'tax_rate' => $tax->rate, 'discount' => 10],
        ];

        $bSubtotal = 0;
        $bTaxTotal = 0;
        $bTotal = 0;
        $pos = 0;
        foreach ($items as $it) {
            $qty   = (float)$it['quantity'];
            $price = (float)$it['unit_price'];
            $rate  = (float)$it['tax_rate'];
            $disc  = (float)$it['discount'];

            $base   = $qty * $price;
            $d      = $base * ($disc / 100);
            $after  = $base - $d;
            $tax    = $after * ($rate / 100);
            $line   = $after + $tax;

            $bSubtotal += $after;
            $bTaxTotal += $tax;
            $bTotal    += $line;

            BudgetItem::create([
                'budget_id'   => $budget->id,
                'product_id'  => $product->id,
                'description' => $it['description'],
                'quantity'    => $qty,
                'unit_price'  => $price,
                'tax_rate'    => $rate,
                'discount'    => $disc,
                'total_line'  => $line,
                'position'    => $pos++,
            ]);
        }
        $budget->update(['subtotal' => $bSubtotal, 'tax_total' => $bTaxTotal, 'total' => $bTotal]);

        // ====== Crea una factura con items ======
        $invoiceNumber = NumberSequence::next('invoice', $user->id); // p.ej. FAC-2025-0001
        preg_match('/^FAC-(\d{4})-(\d{4})$/', $invoiceNumber, $mi);
        $invYear = (int)($mi[1] ?? $year);
        $invSeq  = (int)($mi[2] ?? 1);

        $invoice = Invoice::create([
            'user_id'      => $user->id,
            'client_id'    => $client->id,
            'number'       => $invoiceNumber,
            'sequence'     => $invSeq,
            'year'         => $invYear,
            'date'         => now()->toDateString(),
            'due_date'     => now()->addDays(30)->toDateString(),
            'currency'     => 'EUR',
            'status'       => 'sent', // para que sirva para probar recordatorios
            'notes'        => 'Gracias por su compra.',
            'terms'        => 'Pago por transferencia en 30 días.',
            'public_token' => Str::random(48),
        ]);

        // Reutilizamos los mismos items
        $iSubtotal = 0;
        $iTaxTotal = 0;
        $iTotal = 0;
        $pos = 0;
        foreach ($items as $it) {
            $qty   = (float)$it['quantity'];
            $price = (float)$it['unit_price'];
            $rate  = (float)$it['tax_rate'];
            $disc  = (float)$it['discount'];

            $base   = $qty * $price;
            $d      = $base * ($disc / 100);
            $after  = $base - $d;
            $tax    = $after * ($rate / 100);
            $line   = $after + $tax;

            $iSubtotal += $after;
            $iTaxTotal += $tax;
            $iTotal    += $line;

            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'product_id'  => $product->id,
                'description' => $it['description'],
                'quantity'    => $qty,
                'unit_price'  => $price,
                'tax_rate'    => $rate,
                'discount'    => $disc,
                'total_line'  => $line,
                'position'    => $pos++,
            ]);
        }
        $invoice->update(['subtotal' => $iSubtotal, 'tax_total' => $iTaxTotal, 'total' => $iTotal]);
        $invoice->recalcStatus(); // por si ajusta pending/sent/paid según amount_paid

        $this->command->info('Demo creada:');
        $this->command->info('- Cliente: ' . $client->name . ' (token portal: ' . $client->public_token . ')');
        $this->command->info('- Presupuesto: ' . $budget->number . ' (token: ' . $budget->public_token . ')');
        $this->command->info('- Factura: ' . $invoice->number . ' (token: ' . $invoice->public_token . ')');
    }
}
