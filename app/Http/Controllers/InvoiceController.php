<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Requests\StoreInvoicePaymentRequest;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Budget;
use App\Models\Client;
use App\Models\Product;
use App\Models\NumberSequence;
use App\Models\UserSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Support\Audit;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Invoice::class, 'invoice');
    }

    /** Listado de facturas */
    public function index(Request $request)
    {
        $q = $request->get('q');

        $invoices = Invoice::query()
            ->where('user_id', auth()->id())
            ->with('client')
            ->when($q, function ($b) use ($q) {
                $b->where(function ($w) use ($q) {
                    $w->where('number', 'like', "%{$q}%")
                      ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%{$q}%"));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('invoices.index', compact('invoices', 'q'));
    }

    /** Form de creación */
    public function create()
    {
        $clients = Client::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        // Productos para el selector opcional por línea (solo id y nombre para evitar columnas inexistentes)
        $products = Product::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get(['id','name']);

        return view('invoices.create', compact('clients','products'));
    }

    /** Guardar factura nueva */
    public function store(StoreInvoiceRequest $request)
    {
        $v = $request->validated();

        return DB::transaction(function () use ($v) {
            $number = NumberSequence::next('invoice', auth()->id()); // FAC-YYYY-####
            preg_match('/^FAC-(\d{4})-(\d{4})$/', $number, $m);
            $year     = (int)($m[1] ?? now()->year);
            $sequence = (int)($m[2] ?? 0);

            $invoice = Invoice::create([
                'user_id'      => auth()->id(),
                'client_id'    => $v['client_id'],
                'number'       => $number,
                'sequence'     => $sequence,
                'year'         => $year,
                'date'         => $v['date'] ?? now()->toDateString(),
                'due_date'     => $v['due_date'] ?? null,
                'currency'     => $v['currency'] ?? 'EUR',
                'status'       => 'pending',
                'notes'        => $v['notes'] ?? null,
                'terms'        => $v['terms'] ?? null,
                'public_token' => Str::random(48),
            ]);

            $subtotal = 0; $tax_total = 0; $total = 0; $pos = 0;

            foreach ($v['items'] as $it) {
                $qty   = (float)$it['quantity'];
                $price = (float)$it['unit_price'];
                $rate  = (float)$it['tax_rate'];
                $disc  = (float)($it['discount'] ?? 0);

                $base   = $qty * $price;
                $d      = $base * ($disc / 100);
                $after  = $base - $d;
                $tax    = $after * ($rate / 100);
                $line   = $after + $tax;

                $subtotal += $after; $tax_total += $tax; $total += $line;

                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $it['product_id'] ?? null,
                    'description' => $it['description'],
                    'quantity'    => $qty,
                    'unit_price'  => $price,
                    'tax_rate'    => $rate,
                    'discount'    => $disc,
                    'total_line'  => $line,
                    'position'    => $pos++,
                ]);
            }

            $invoice->update(compact('subtotal', 'tax_total', 'total'));
            $invoice->recalcStatus();

            Audit::record('invoice.created','invoice',$invoice->id,[
                'number'=>$invoice->number
            ]);

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('ok', 'Factura creada.');
        });
    }

    /** Ver factura */
    public function show(Invoice $invoice)
    {
        $invoice->load('client', 'items', 'payments');
        return view('invoices.show', compact('invoice'));
    }

    /** Editar factura */
    public function edit(Invoice $invoice)
    {
        $invoice->load('items');

        $clients = Client::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')->get();

        // Productos para el selector opcional por línea (solo id y nombre)
        $products = Product::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get(['id','name']);

        return view('invoices.edit', compact('invoice','clients','products'));
    }

    /** Actualizar factura */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $v = $request->validated();

        return DB::transaction(function () use ($v, $invoice) {
            $invoice->update([
                'client_id' => $v['client_id'],
                'date'      => $v['date'] ?? $invoice->date,
                'due_date'  => $v['due_date'] ?? null,
                'currency'  => $v['currency'] ?? 'EUR',
                'notes'     => $v['notes'] ?? null,
                'terms'     => $v['terms'] ?? null,
            ]);

            // Reemplazar items
            $invoice->items()->delete();

            $subtotal = 0; $tax_total = 0; $total = 0; $pos = 0;

            foreach ($v['items'] as $it) {
                $qty   = (float)$it['quantity'];
                $price = (float)$it['unit_price'];
                $rate  = (float)$it['tax_rate'];
                $disc  = (float)($it['discount'] ?? 0);

                $base   = $qty * $price;
                $d      = $base * ($disc / 100);
                $after  = $base - $d;
                $tax    = $after * ($rate / 100);
                $line   = $after + $tax;

                $subtotal += $after; $tax_total += $tax; $total += $line;

                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $it['product_id'] ?? null,
                    'description' => $it['description'],
                    'quantity'    => $qty,
                    'unit_price'  => $price,
                    'tax_rate'    => $rate,
                    'discount'    => $disc,
                    'total_line'  => $line,
                    'position'    => $pos++,
                ]);
            }

            $invoice->update(compact('subtotal', 'tax_total', 'total'));
            $invoice->recalcStatus();

            Audit::record('invoice.updated','invoice',$invoice->id,[
                'number'=>$invoice->number
            ]);

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('ok', 'Factura actualizada.');
        });
    }

    /** Eliminar */
    public function destroy(Invoice $invoice)
    {
        $id  = $invoice->id;
        $num = $invoice->number;
        $invoice->delete();

        Audit::record('invoice.deleted','invoice',$id, ['number'=>$num]);

        return redirect()->route('invoices.index')->with('ok', 'Factura eliminada.');
    }

    /** Descargar PDF (auditoría incluida) */
    public function pdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load('client', 'items', 'payments');

        $view = \App\Support\PdfTemplates::invoiceView($invoice->user_id);
        $pdf = Pdf::loadView($view, compact('invoice'));

        Audit::record('invoice.pdf','invoice',$invoice->id,['number'=>$invoice->number]);

        return $pdf->download($invoice->number . '.pdf');
    }

    /** Enviar por email (adjunta PDF) */
    public function email(Request $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $data = $request->validate([
            'to'      => ['required', 'email:rfc'],
            'cc'      => ['nullable', 'email:rfc'],
            'bcc'     => ['nullable', 'email:rfc'],
            'subject' => ['nullable', 'string', 'max:190'],
        ]);

        if (empty($invoice->public_token)) {
            $invoice->public_token = Str::random(48);
            $invoice->save();
        }

        $invoice->load('client', 'items', 'payments');
        $company = UserSetting::query()->where('user_id', $invoice->user_id)->first();

        $pdfBin = Pdf::loadView('pdf.invoice', compact('invoice', 'company'))->output();

        $mailable = new InvoiceMail($invoice, $pdfBin);
        if (!empty($data['subject'])) {
            $mailable->subject($data['subject']);
        } else {
            $mailable->subject('Factura ' . $invoice->number);
        }

        $mail = Mail::to($data['to']);
        if (!empty($data['cc']))  { $mail->cc($data['cc']); }
        if (!empty($data['bcc'])) { $mail->bcc($data['bcc']); }

        app()->environment('local') ? $mail->send($mailable) : $mail->queue($mailable);

        if (empty($invoice->sent_at)) {
            $invoice->sent_at = now();
            if ($invoice->status !== 'paid') {
                $invoice->status = 'sent';
            }
            $invoice->save();
        }

        Audit::record('invoice.emailed','invoice',$invoice->id,[
            'to'=>$data['to'],'cc'=>$data['cc'] ?? null,'bcc'=>$data['bcc'] ?? null
        ]);

        return back()->with('ok', 'Factura enviada por email.');
    }

    /** Marcar como enviada (sin email) */
    public function markSent(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if (empty($invoice->public_token)) {
            $invoice->public_token = Str::random(48);
        }
        $invoice->sent_at = now();
        if ($invoice->status !== 'paid') {
            $invoice->status = 'sent';
        }
        $invoice->save();

        Audit::record('invoice.mark_sent','invoice',$invoice->id);

        return back()->with('ok', 'Factura marcada como enviada.');
    }

    /** Convertir presupuesto → factura */
    public function convertFromBudget(Budget $budget)
    {
        $this->authorize('create', Invoice::class);

        $budget->load('items');

        return DB::transaction(function () use ($budget) {
            $number = NumberSequence::next('invoice', auth()->id());
            preg_match('/^FAC-(\d{4})-(\d{4})$/', $number, $m);
            $year     = (int)($m[1] ?? now()->year);
            $sequence = (int)($m[2] ?? 0);

            $invoice = Invoice::create([
                'user_id'      => auth()->id(),
                'client_id'    => $budget->client_id,
                'number'       => $number,
                'sequence'     => $sequence,
                'year'         => $year,
                'date'         => now()->toDateString(),
                'due_date'     => $budget->due_date,
                'currency'     => $budget->currency,
                'status'       => 'pending',
                'notes'        => $budget->notes,
                'terms'        => $budget->terms,
                'public_token' => Str::random(48),
            ]);

            $subtotal = 0; $tax_total = 0; $total = 0; $pos = 0;

            foreach ($budget->items as $it) {
                $qty   = (float)$it->quantity;
                $price = (float)$it->unit_price;
                $rate  = (float)$it->tax_rate;
                $disc  = (float)$it->discount;

                $base   = $qty * $price;
                $d      = $base * ($disc / 100);
                $after  = $base - $d;
                $tax    = $after * ($rate / 100);
                $line   = $after + $tax;

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

            $invoice->update(compact('subtotal', 'tax_total', 'total'));
            $invoice->recalcStatus();

            if ($budget->status !== 'accepted') {
                $budget->update(['status' => 'accepted']);
            }

            Audit::record('invoice.created_from_budget','invoice',$invoice->id,[
                'budget_id'=>$budget->id,'invoice_number'=>$invoice->number
            ]);

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('ok', 'Factura creada desde presupuesto.');
        });
    }

    /** Registrar pago */
    public function registerPayment(StoreInvoicePaymentRequest $request, Invoice $invoice)
    {
        // Autorización específica para pagos (evita 403)
        $this->authorize('pay', $invoice);

        $v = $request->validated();

        return DB::transaction(function () use ($v, $invoice) {
            $payment = InvoicePayment::create([
                'invoice_id'   => $invoice->id,
                'amount'       => $v['amount'],
                'payment_date' => $v['payment_date'],
                'method'       => $v['method'],
                'notes'        => $v['notes'] ?? null,
            ]);

            $sum = $invoice->payments()->sum('amount');
            $invoice->update(['amount_paid' => $sum]);
            $invoice->recalcStatus();

            Audit::record('invoice.payment_registered','invoice',$invoice->id,[
                'payment_id'=>$payment->id,'amount'=>$payment->amount,'method'=>$payment->method
            ]);

            return back()->with('ok', 'Pago registrado.');
        });
    }
}
