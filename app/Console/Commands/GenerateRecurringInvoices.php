<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\RecurringInvoice;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\NumberSequence;

class GenerateRecurringInvoices extends Command
{
    protected $signature = 'invoices:generate-recurring {--date=} {--user_id=}';
    protected $description = 'Genera facturas desde plantillas recurrentes cuyo next_run_date sea hoy o anterior';

    public function handle(): int
    {
        $runDate = $this->option('date') ?: now()->toDateString();
        $userId  = $this->option('user_id'); // opcional

        $q = RecurringInvoice::query()
            ->where('status', 'active')
            ->whereDate('next_run_date', '<=', $runDate);

        if ($userId) $q->where('user_id', $userId);

        $count = 0;

        $q->chunkById(50, function ($templates) use (&$count, $runDate) {
            foreach ($templates as $tpl) {
                DB::transaction(function () use ($tpl, &$count, $runDate) {
                    $series = 'FAC';
                    $year   = (int) date('Y', strtotime($runDate));

                    // Numeración FAC-YYYY-####
                    $number = NumberSequence::next('invoice', $series, $year);

                    $sequence = 0; $parsedYear = $year;
                    if (preg_match('/^([A-Z]+)-(\d{4})-(\d{4})$/', $number, $m)) {
                        $parsedYear = (int)($m[2] ?? $year);
                        $sequence   = (int)($m[3] ?? 0);
                    }

                    $invoice = Invoice::create([
                        'user_id'     => $tpl->user_id,
                        'client_id'   => $tpl->client_id,
                        'number'      => $number,
                        'sequence'    => $sequence,
                        'year'        => $parsedYear,
                        'date'        => $runDate,
                        'due_date'    => date('Y-m-d', strtotime($runDate.' +15 days')),
                        'currency'    => $tpl->currency ?? 'EUR',
                        'status'      => 'pending',
                        'public_token'=> Str::random(48),
                        'notes'       => $tpl->public_notes,
                        'terms'       => $tpl->terms,
                    ]);

                    $subtotal=0; $tax_total=0; $total=0; $pos=0;

                    foreach ($tpl->items as $it) {
                        $qty  = (float)$it->quantity;
                        $unit = (float)$it->unit_price;
                        $disc = (float)$it->discount;
                        $tax  = (float)$it->tax_rate;

                        $base = $qty * $unit;
                        $discAmt = $base * ($disc/100);
                        $afterDisc = $base - $discAmt;
                        $taxAmt = $afterDisc * ($tax/100);
                        $lineTotal = $afterDisc + $taxAmt;

                        InvoiceItem::create([
                            'invoice_id'  => $invoice->id,
                            'product_id'  => $it->product_id,
                            'description' => $it->description,
                            'quantity'    => $qty,
                            'unit_price'  => $unit,
                            'tax_rate'    => $tax,
                            'discount'    => $disc,
                            'total_line'  => $lineTotal,
                            'position'    => $pos++,
                        ]);

                        $subtotal += $afterDisc;
                        $tax_total += $taxAmt;
                        $total    += $lineTotal;
                    }

                    $invoice->update(compact('subtotal','tax_total','total'));

                    // actualizar plantilla
                    $tpl->last_run_date   = $runDate;
                    $tpl->last_invoice_id = $invoice->id;
                    $tpl->bumpNextRunDate();
                    $tpl->save();

                    $count++;
                    $this->info("Generada factura {$invoice->number} del recurring {$tpl->id}");
                });
            }
        });

        $this->info("Total generadas: {$count}");
        return self::SUCCESS;
    }
}