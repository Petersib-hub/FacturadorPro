<?php

namespace App\Http\Controllers;

use App\Models\RecurringInvoice;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\NumberSequence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\UniqueConstraintViolationException;

class RecurringInvoiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(RecurringInvoice::class, 'recurring_invoice');
    }

    public function index(Request $request)
    {
        $q = $request->get('q');

        $list = RecurringInvoice::query()
            ->where('user_id', auth()->id())
            ->when($q, function ($b) use ($q) {
                $b->where('name','like',"%{$q}%")
                  ->orWhereHas('client', fn($c) => $c->where('name','like',"%{$q}%"));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('recurring.index', compact('list','q'));
    }

    public function create()
    {
        $clients  = Client::query()->where('user_id', auth()->id())->orderBy('name')->get();
        $products = Product::query()->where('user_id', auth()->id())->orderBy('name')->get(['id','name']);
        return view('recurring.create', compact('clients','products'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'name'        => ['required','string','max:190'],
            'client_id'   => ['required','exists:clients,id'],
            'start_date'  => ['required','date'],
            'interval'    => ['required','in:daily,weekly,monthly,yearly,custom'],
            'interval_n'  => ['nullable','integer','min:1','max:365'],
            'currency'    => ['required','string','size:3'],
            'notes'       => ['nullable','string','max:5000'],
            'terms'       => ['nullable','string','max:5000'],
            'items'       => ['required','array','min:1'],
            'items.*.product_id'  => ['nullable','exists:products,id'],
            'items.*.description' => ['required','string','max:500'],
            'items.*.quantity'    => ['required','numeric','min:0.001'],
            'items.*.unit_price'  => ['required','numeric','min:0'],
            'items.*.tax_rate'    => ['required','numeric','min:0','max:999.999'],
            'items.*.discount'    => ['nullable','numeric','min:0','max:100'],
        ]);

        $rec = RecurringInvoice::create([
            'user_id'     => auth()->id(),
            'name'        => $v['name'],
            'client_id'   => $v['client_id'],
            'start_date'  => $v['start_date'],
            'next_run'    => $v['start_date'],
            'interval'    => $v['interval'],
            'interval_n'  => $v['interval_n'] ?? null,
            'currency'    => $v['currency'],
            'notes'       => $v['notes'] ?? null,
            'terms'       => $v['terms'] ?? null,
            'is_paused'   => false,
        ]);

        $pos = 0;
        foreach ($v['items'] as $it) {
            $rec->items()->create([
                'product_id'  => $it['product_id'] ?? null,
                'description' => $it['description'],
                'quantity'    => (float)$it['quantity'],
                'unit_price'  => (float)$it['unit_price'],
                'tax_rate'    => (float)$it['tax_rate'],
                'discount'    => (float)($it['discount'] ?? 0),
                'position'    => $pos++,
            ]);
        }

        return redirect()->route('recurring-invoices.index')->with('ok','Plantilla creada.');
    }

    public function show(RecurringInvoice $recurring_invoice)
    {
        $recurring_invoice->load('client','items');
        return view('recurring.show', ['rec' => $recurring_invoice]);
    }

    public function edit(RecurringInvoice $recurring_invoice)
    {
        $recurring_invoice->load('items');
        $clients  = Client::query()->where('user_id', auth()->id())->orderBy('name')->get();
        $products = Product::query()->where('user_id', auth()->id())->orderBy('name')->get(['id','name']);

        return view('recurring.edit', ['rec'=>$recurring_invoice,'clients'=>$clients,'products'=>$products]);
    }

    public function update(Request $request, RecurringInvoice $recurring_invoice)
    {
        $v = $request->validate([
            'name'        => ['required','string','max:190'],
            'client_id'   => ['required','exists:clients,id'],
            'start_date'  => ['required','date'],
            'next_run'    => ['nullable','date'],
            'interval'    => ['required','in:daily,weekly,monthly,yearly,custom'],
            'interval_n'  => ['nullable','integer','min:1','max:365'],
            'currency'    => ['required','string','size:3'],
            'notes'       => ['nullable','string','max:5000'],
            'terms'       => ['nullable','string','max:5000'],
            'items'       => ['required','array','min:1'],
            'items.*.product_id'  => ['nullable','exists:products,id'],
            'items.*.description' => ['required','string','max:500'],
            'items.*.quantity'    => ['required','numeric','min:0.001'],
            'items.*.unit_price'  => ['required','numeric','min:0'],
            'items.*.tax_rate'    => ['required','numeric','min:0','max:999.999'],
            'items.*.discount'    => ['nullable','numeric','min:0','max:100'],
        ]);

        $recurring_invoice->update([
            'name'       => $v['name'],
            'client_id'  => $v['client_id'],
            'start_date' => $v['start_date'],
            'next_run'   => $v['next_run'] ?? $recurring_invoice->next_run,
            'interval'   => $v['interval'],
            'interval_n' => $v['interval_n'] ?? null,
            'currency'   => $v['currency'],
            'notes'      => $v['notes'] ?? null,
            'terms'      => $v['terms'] ?? null,
        ]);

        $recurring_invoice->items()->delete();
        $pos = 0;
        foreach ($v['items'] as $it) {
            $recurring_invoice->items()->create([
                'product_id'  => $it['product_id'] ?? null,
                'description' => $it['description'],
                'quantity'    => (float)$it['quantity'],
                'unit_price'  => (float)$it['unit_price'],
                'tax_rate'    => (float)$it['tax_rate'],
                'discount'    => (float)($it['discount'] ?? 0),
                'position'    => $pos++,
            ]);
        }

        return redirect()->route('recurring-invoices.edit',$recurring_invoice)->with('ok','Plantilla actualizada.');
    }

    public function destroy(RecurringInvoice $recurring_invoice)
    {
        $recurring_invoice->delete();
        return redirect()->route('recurring-invoices.index')->with('ok','Plantilla eliminada.');
    }

    public function pause(RecurringInvoice $recurring_invoice)
    {
        $recurring_invoice->update(['is_paused' => true]);
        return back()->with('ok','Plantilla pausada.');
    }

    public function resume(RecurringInvoice $recurring_invoice)
    {
        $recurring_invoice->update(['is_paused' => false]);
        return back()->with('ok','Plantilla reanudada.');
    }

    /**
     * GENERAR AHORA → crea una factura REAL consumiendo la secuencia global FAC-YYYY-####.
     */
    public function runNow(RecurringInvoice $recurring_invoice)
    {
        $this->authorize('update', $recurring_invoice);
        $recurring_invoice->load('items');

        return DB::transaction(function () use ($recurring_invoice) {
            $attempts = 0;

            // La fecha de la factura generada (hoy por defecto)
            $emitDate = now()->toDateString();
            $year     = (int) date('Y', strtotime($emitDate));
            $series   = 'FAC'; // ← MISMA SERIE QUE FACTURAS NORMALES

            retry_create:
            try {
                // 1) Numeración única y global para facturas
                $number = NumberSequence::next('invoice', $series, $year); // FAC-YYYY-####
                if (preg_match('/^([A-Z]+)-(\d{4})-(\d{4})$/', $number, $m)) {
                    $year     = (int) $m[2];
                    $sequence = (int) $m[3];
                } else {
                    // fallback: por si alguien cambió el formato en NumberSequence
                    $sequence = (int) substr($number, -4);
                }

                // 2) Crear factura
                $invoice = Invoice::create([
                    'user_id'      => auth()->id(),
                    'client_id'    => $recurring_invoice->client_id,
                    'number'       => $number,
                    'sequence'     => $sequence,
                    'year'         => $year,
                    'date'         => $emitDate,
                    'due_date'     => null,
                    'currency'     => $recurring_invoice->currency ?? 'EUR',
                    'status'       => 'pending',
                    'notes'        => $recurring_invoice->notes,
                    'terms'        => $recurring_invoice->terms,
                    'public_token' => Str::random(48),
                ]);
            } catch (UniqueConstraintViolationException $e) {
                if ($attempts++ < 3) goto retry_create;
                throw $e;
            }

            // 3) Copiar ítems de la plantilla
            $subtotal = 0; $tax_total = 0; $total = 0; $pos = 0;
            foreach ($recurring_invoice->items as $it) {
                $qty   = (float) $it->quantity;
                $price = (float) $it->unit_price;
                $rate  = (float) $it->tax_rate;
                $disc  = (float) $it->discount;

                $base  = $qty * $price;
                $d     = $base * ($disc / 100);
                $after = $base - $d;
                $tax   = $after * ($rate / 100);
                $line  = $after + $tax;

                $subtotal += $after; $tax_total += $tax; $total += $line;

                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $it->product_id,
                    'description' => $it->description,
                    'quantity'    => $qty,
                    'unit_price'  => $price,
                    'tax_rate'    => $rate,
                    'discount'    => $disc,
                    'total_line'  => $line,
                    'position'    => $pos++,
                ]);
            }

            $invoice->update(compact('subtotal','tax_total','total'));
            $invoice->recalcStatus();

            // 4) Movimiento de la "siguiente ejecución"
            $recurring_invoice->last_run_at = now();
            $recurring_invoice->next_run    = $this->calcNextRun($recurring_invoice);
            $recurring_invoice->save();

            return redirect()->route('invoices.show', $invoice)->with('ok','Factura generada.');
        });
    }

    /**
     * Calcula la siguiente fecha de ejecución según la plantilla.
     */
    protected function calcNextRun(RecurringInvoice $rec): string
    {
        $n = max(1, (int) ($rec->interval_n ?? 1));
        return match ($rec->interval) {
            'daily'   => now()->addDays($n)->toDateString(),
            'weekly'  => now()->addWeeks($n)->toDateString(),
            'monthly' => now()->addMonths($n)->toDateString(),
            'yearly'  => now()->addYears($n)->toDateString(),
            'custom'  => now()->addDays($n)->toDateString(),
            default   => now()->addMonths(1)->toDateString(),
        };
    }
}