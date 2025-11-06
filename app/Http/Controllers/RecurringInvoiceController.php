<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringInvoiceRequest;
use App\Http\Requests\UpdateRecurringInvoiceRequest;
use App\Models\RecurringInvoice;
use App\Models\RecurringInvoiceItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\TaxRate;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\NumberSequence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecurringInvoiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(RecurringInvoice::class, 'recurring_invoice');
    }

    /** Listado */
    public function index(Request $request)
    {
        $q      = $request->get('q');
        $status = $request->get('status');
        $freq   = $request->get('frequency');

        $list = RecurringInvoice::query()
            ->where('user_id', auth()->id())
            ->with('client:id,name')
            ->when($q, function ($b) use ($q) {
                $b->whereHas('client', fn($c) => $c->where('name', 'like', "%{$q}%"));
            })
            ->when($status, fn($b) => $b->where('status', $status))
            ->when($freq, fn($b) => $b->where('frequency', $freq))
            ->orderBy('next_run_date')
            ->paginate(12)
            ->withQueryString();

        return view('recurring_invoices.index', compact('list', 'q', 'status', 'freq'));
    }

    /** Form crear */
    public function create()
    {
        $clients  = Client::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')->get(['id','name']);

        $products = Product::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')->get(['id','name','unit_price','tax_rate']);

        $taxRates = TaxRate::forAuthUser()
            ->orderByDesc('is_default')
            ->orderBy('rate')
            ->get();

        return view('recurring_invoices.create', compact('clients', 'products', 'taxRates'));
    }

    /** Guardar */
    public function store(StoreRecurringInvoiceRequest $request)
    {
        $data = $request->validated();

        $ri = DB::transaction(function () use ($data) {
            $ri = RecurringInvoice::create([
                'user_id'       => auth()->id(),
                'client_id'     => $data['client_id'],
                'start_date'    => $data['start_date'] ?? now()->toDateString(),
                'frequency'     => $data['frequency'],
                'currency'      => $data['currency'] ?? 'EUR',
                'next_run_date' => $data['next_run_date'] ?? now()->addMonth()->toDateString(),
                'status'        => 'active',
                'public_notes'  => $data['public_notes'] ?? null,
                'terms'         => $data['terms'] ?? null,
            ]);

            $pos = 0;
            foreach ($data['items'] as $it) {
                $qty   = (float) ($it['quantity']   ?? 0);
                $price = (float) ($it['unit_price'] ?? 0);
                $tax   = (float) ($it['tax_rate']   ?? 0);
                $disc  = (float) ($it['discount']   ?? 0);

                RecurringInvoiceItem::create([
                    'recurring_invoice_id' => $ri->id,
                    'product_id'  => $it['product_id'] ?? null,
                    'description' => $it['description'],
                    'quantity'    => $qty,
                    'unit_price'  => $price,
                    'tax_rate'    => $tax,
                    'discount'    => $disc,
                    'position'    => $it['position'] ?? $pos++,
                ]);
            }

            return $ri;
        });

        return redirect()
            ->route('recurring-invoices.show', $ri)
            ->with('ok', 'Plantilla creada.');
    }

    /** Ver */
    public function show(RecurringInvoice $recurring_invoice)
    {
        $recurring_invoice->load([
            'client:id,name',
            'items' => fn($q) => $q->orderBy('position'),
        ]);

        return view('recurring_invoices.show', ['ri' => $recurring_invoice]);
    }

    /** Editar */
    public function edit(RecurringInvoice $recurring_invoice)
    {
        $recurring_invoice->load(['items' => fn($q) => $q->orderBy('position')]);

        $clients = Client::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')->get(['id','name']);

        $products = Product::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')->get(['id','name','unit_price','tax_rate']);

        $taxRates = TaxRate::forAuthUser()
            ->orderByDesc('is_default')
            ->orderBy('rate')
            ->get();

        return view('recurring_invoices.edit', [
            'ri'        => $recurring_invoice,
            'clients'   => $clients,
            'products'  => $products,
            'taxRates'  => $taxRates,
        ]);
    }

    /** Actualizar */
    public function update(UpdateRecurringInvoiceRequest $request, RecurringInvoice $recurring_invoice)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $recurring_invoice) {
            $recurring_invoice->update([
                'client_id'     => $data['client_id'],
                'frequency'     => $data['frequency'],
                'currency'      => $data['currency'] ?? $recurring_invoice->currency,
                'next_run_date' => $data['next_run_date'] ?? $recurring_invoice->next_run_date,
                'public_notes'  => $data['public_notes'] ?? null,
                'terms'         => $data['terms'] ?? null,
            ]);

            $recurring_invoice->items()->delete();

            $pos = 0;
            foreach ($data['items'] as $it) {
                $qty   = (float) ($it['quantity']   ?? 0);
                $price = (float) ($it['unit_price'] ?? 0);
                $tax   = (float) ($it['tax_rate']   ?? 0);
                $disc  = (float) ($it['discount']   ?? 0);

                RecurringInvoiceItem::create([
                    'recurring_invoice_id' => $recurring_invoice->id,
                    'product_id'  => $it['product_id'] ?? null,
                    'description' => $it['description'],
                    'quantity'    => $qty,
                    'unit_price'  => $price,
                    'tax_rate'    => $tax,
                    'discount'    => $disc,
                    'position'    => $it['position'] ?? $pos++,
                ]);
            }
        });

        return redirect()
            ->route('recurring-invoices.show', $recurring_invoice)
            ->with('ok', 'Plantilla actualizada.');
    }

    /** Eliminar */
    public function destroy(RecurringInvoice $recurring_invoice)
    {
        $recurring_invoice->delete();
        return redirect()
            ->route('recurring-invoices.index')
            ->with('ok', 'Plantilla eliminada.');
    }

    /** Pausar */
    public function pause(RecurringInvoice $recurring_invoice)
    {
        $recurring_invoice->update(['status' => 'paused']);
        return back()->with('ok', 'Plantilla pausada.');
    }

    /** Reanudar */
    public function resume(RecurringInvoice $recurring_invoice)
    {
        $recurring_invoice->update(['status' => 'active']);
        return back()->with('ok', 'Plantilla reanudada.');
    }

    /** Ejecutar ahora → genera factura inmediata */
    public function runNow(RecurringInvoice $recurring_invoice)
    {
        $ri = $recurring_invoice->load(['items' => fn($q) => $q->orderBy('position')]);

        $invoice = DB::transaction(function () use ($ri) {
            $number = NumberSequence::next('invoice', $ri->user_id);
            // Extrae año/seq (FAC-YYYY-####)
            preg_match('/^FAC-(\d{4})-(\d{4})$/', $number, $m);
            $year = (int)($m[1] ?? now()->year);
            $seq  = (int)($m[2] ?? 0);

            $invoice = Invoice::create([
                'user_id'      => $ri->user_id,
                'client_id'    => $ri->client_id,
                'number'       => $number,
                'sequence'     => $seq,
                'year'         => $year,
                'date'         => now()->toDateString(),
                'due_date'     => now()->addDays(15)->toDateString(),
                'currency'     => $ri->currency,
                'status'       => 'pending',
                'public_token' => Str::random(48),
                'notes'        => $ri->public_notes,
                'terms'        => $ri->terms,
            ]);

            $subtotal = 0; $tax_total = 0; $total = 0; $pos = 0;

            foreach ($ri->items as $it) {
                $qty   = (float) $it->quantity;
                $unit  = (float) $it->unit_price;
                $tax   = (float) $it->tax_rate;
                $disc  = (float) $it->discount;

                $base     = $qty * $unit;
                $discAmt  = $base * ($disc / 100);
                $after    = $base - $discAmt;
                $taxAmt   = $after * ($tax / 100);
                $lineTotal= $after + $taxAmt;

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

                $subtotal += $after; $tax_total += $taxAmt; $total += $lineTotal;
            }

            $invoice->update(compact('subtotal','tax_total','total'));

            $ri->last_invoice_id = $invoice->id;
            $ri->last_run_date   = now()->toDateString();
            // Recalcular próxima fecha según frecuencia
            $ri->bumpNextRunDate();
            $ri->save();

            return $invoice;
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('ok', 'Factura generada desde la plantilla.');
    }

    /** Duplicar plantilla */
    public function duplicate(RecurringInvoice $recurring_invoice)
    {
        $ri = $recurring_invoice->load(['items' => fn($q) => $q->orderBy('position')]);

        $copy = DB::transaction(function () use ($ri) {
            $new = RecurringInvoice::create([
                'user_id'       => $ri->user_id,
                'client_id'     => $ri->client_id,
                'start_date'    => now()->toDateString(),
                'frequency'     => $ri->frequency,
                'currency'      => $ri->currency,
                'next_run_date' => now()->toDateString(),
                'status'        => 'active',
                'public_notes'  => $ri->public_notes,
                'terms'         => $ri->terms,
            ]);

            foreach ($ri->items as $it) {
                RecurringInvoiceItem::create([
                    'recurring_invoice_id' => $new->id,
                    'product_id'  => $it->product_id,
                    'description' => $it->description,
                    'quantity'    => (float) $it->quantity,
                    'unit_price'  => (float) $it->unit_price,
                    'tax_rate'    => (float) $it->tax_rate,
                    'discount'    => (float) $it->discount,
                    'position'    => (int) $it->position,
                ]);
            }

            return $new;
        });

        return redirect()
            ->route('recurring-invoices.edit', $copy)
            ->with('ok', 'Plantilla duplicada. Edita y guarda.');
    }
}
