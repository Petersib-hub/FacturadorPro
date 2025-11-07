<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Mail\BudgetMail;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Client;
use App\Models\NumberSequence;
use App\Models\Product;
use App\Models\TaxRate;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\UniqueConstraintViolationException;

class BudgetController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Budget::class, 'budget');
    }

    public function index(Request $request)
    {
        $q = $request->get('q');

        $budgets = Budget::query()
            ->where('user_id', auth()->id())                 // tenant
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

        return view('budgets.index', compact('budgets', 'q'));
    }

    public function create()
    {
        $clients = Client::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')->get();

        $products = Product::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')->get();

        $taxRates = TaxRate::forAuthUser()
            ->orderByDesc('is_default')
            ->orderBy('rate')
            ->get();

        return view('budgets.create', compact('clients', 'products', 'taxRates'));
    }

    public function store(StoreBudgetRequest $request)
    {
        $validated = $request->validated();

        // Defaults seguros
        $validated['currency'] = $validated['currency'] ?? 'EUR';
        $validated['status']   = 'draft';
        $validated['date']     = $validated['date'] ?? now()->toDateString();
        $validated['due_date'] = $validated['due_date'] ?? now()->addDays(14)->toDateString();

        return DB::transaction(function () use ($validated) {
            // Si existe NumberSequence, generamos número atómico aquí; si no, dejar que el modelo lo haga.
            $number = null;
            $year = (int) (date('Y', strtotime($validated['date'])));
            $sequence = null;

            if (class_exists(NumberSequence::class)) {
                $number = NumberSequence::next('budget', auth()->id()); // PRES-YYYY-####
                if (preg_match('/^PRES-(\d{4})-(\d{4})$/', $number, $m)) {
                    $year     = (int)($m[1] ?? $year);
                    $sequence = (int)($m[2] ?? 0);
                }
            }

            $data = [
                'user_id'      => auth()->id(),
                'client_id'    => $validated['client_id'],
                'number'       => $number,   // si es null, el modelo lo genera en booted()
                'sequence'     => $sequence, // puede quedar null; booted() lo completa
                'year'         => $year,
                'date'         => $validated['date'],
                'due_date'     => $validated['due_date'],
                'currency'     => $validated['currency'],
                'status'       => 'draft',
                'notes'        => $validated['notes'] ?? null,
                'terms'        => $validated['terms'] ?? null,
                'public_token' => Str::random(48),
            ];

            // Retry anti-colisión por índice único budgets_number_unique
            $attempts = 0;
            start_create:
            try {
                $budget = Budget::create($data);
            } catch (UniqueConstraintViolationException $e) {
                if ($attempts++ < 2) {
                    // Genera un nuevo número y reintenta
                    if (class_exists(NumberSequence::class)) {
                        $number = NumberSequence::next('budget', auth()->id());
                        if (preg_match('/^PRES-(\d{4})-(\d{4})$/', $number, $m)) {
                            $year     = (int)($m[1] ?? $year);
                            $sequence = (int)($m[2] ?? 0);
                        }
                        $data['number'] = $number;
                        $data['year'] = $year;
                        $data['sequence'] = $sequence;
                    } else {
                        // Forzamos al modelo a regenerar limpiando number/sequence
                        $data['number'] = null;
                        $data['sequence'] = null;
                    }
                    goto start_create;
                }
                throw $e;
            }

            $subtotal = 0;
            $tax_total = 0;
            $total = 0;
            $pos = 0;

            foreach ($validated['items'] as $it) {
                $qty   = (float) $it['quantity'];
                $price = (float) $it['unit_price'];
                $rate  = (float) $it['tax_rate'];
                $disc  = (float) ($it['discount'] ?? 0);

                $line_base        = $qty * $price;
                $line_disc        = $line_base * ($disc / 100);
                $line_after_disc  = $line_base - $line_disc;
                $line_tax         = $line_after_disc * ($rate / 100);
                $line_total       = $line_after_disc + $line_tax;

                $subtotal += $line_after_disc;
                $tax_total += $line_tax;
                $total    += $line_total;

                BudgetItem::create([
                    'budget_id'   => $budget->id,
                    'product_id'  => $it['product_id'] ?? null,
                    'description' => $it['description'],
                    'quantity'    => $qty,
                    'unit_price'  => $price,
                    'tax_rate'    => $rate,
                    'discount'    => $disc,
                    'total_line'  => $line_total,
                    'position'    => $pos++,
                ]);
            }

            $budget->update(compact('subtotal', 'tax_total', 'total'));

            return redirect()
                ->route('budgets.show', $budget)
                ->with('ok', 'Presupuesto creado.');
        });
    }

    public function show(Budget $budget)
    {
        $budget->load('client', 'items');
        return view('budgets.show', compact('budget'));
    }

    public function edit(Budget $budget)
    {
        $budget->load('items');

        $clients = Client::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')->get();

        $products = Product::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')->get();

        $taxRates = TaxRate::forAuthUser()
            ->orderByDesc('is_default')
            ->orderBy('rate')
            ->get();

        return view('budgets.edit', compact('budget', 'clients', 'products', 'taxRates'));
    }

    public function update(UpdateBudgetRequest $request, Budget $budget)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated, $budget) {
            $budget->update([
                'client_id' => $validated['client_id'],
                'date'      => $validated['date'] ?? $budget->date,
                'due_date'  => $validated['due_date'] ?? $budget->due_date ?? now()->addDays(14)->toDateString(),
                'currency'  => $validated['currency'] ?? 'EUR',
                'notes'     => $validated['notes'] ?? null,
                'terms'     => $validated['terms'] ?? null,
            ]);

            // Reemplazar items
            $budget->items()->delete();

            $subtotal = 0;
            $tax_total = 0;
            $total = 0;
            $pos = 0;

            foreach ($validated['items'] as $it) {
                $qty   = (float) $it['quantity'];
                $price = (float) $it['unit_price'];
                $rate  = (float) $it['tax_rate'];
                $disc  = (float) ($it['discount'] ?? 0);

                $line_base        = $qty * $price;
                $line_disc        = $line_base * ($disc / 100);
                $line_after_disc  = $line_base - $line_disc;
                $line_tax         = $line_after_disc * ($rate / 100);
                $line_total       = $line_after_disc + $line_tax;

                $subtotal += $line_after_disc;
                $tax_total += $line_tax;
                $total    += $line_total;

                BudgetItem::create([
                    'budget_id'   => $budget->id,
                    'product_id'  => $it['product_id'] ?? null,
                    'description' => $it['description'],
                    'quantity'    => $qty,
                    'unit_price'  => $price,
                    'tax_rate'    => $rate,
                    'discount'    => $disc,
                    'total_line'  => $line_total,
                    'position'    => $pos++,
                ]);
            }

            $budget->update(compact('subtotal', 'tax_total', 'total'));

            return redirect()
                ->route('budgets.show', $budget)
                ->with('ok', 'Presupuesto actualizado.');
        });
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        return redirect()->route('budgets.index')->with('ok', 'Presupuesto eliminado.');
    }

    // === PDF ===
    public function pdf(Budget $budget)
    {
        $budget->load('client', 'items');

        // Selección dinámica de plantilla
        $view = \App\Support\PdfTemplates::budgetView($budget->user_id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, compact('budget'));
        return $pdf->download($budget->number . '.pdf');
    }

    // === Envío por email (adjunta PDF) ===
    public function email(Request $request, Budget $budget)
    {
        // En local permite dominios sin DNS y añadimos compatibilidad con cc/bcc/subject
        $data = $request->validate([
            'to'      => ['required', 'email:rfc'],
            'cc'      => ['nullable', 'email:rfc'],
            'bcc'     => ['nullable', 'email:rfc'],
            'subject' => ['nullable', 'string', 'max:190'],
        ]);

        if (empty($budget->public_token)) {
            $budget->public_token = Str::random(48);
            $budget->save();
        }

        $budget->load('client', 'items');
        $company = UserSetting::query()->where('user_id', $budget->user_id)->first();

        // ✅ Usar plantilla dinámica (igual que en pdf())
        $view = \App\Support\PdfTemplates::budgetView($budget->user_id);
        $pdf = Pdf::loadView($view, compact('budget', 'company'))->output();

        $mailable = new BudgetMail($budget, $pdf);
        if (!empty($data['subject'])) {
            $mailable->subject($data['subject']);
        } else {
            $mailable->subject('Presupuesto ' . $budget->number);
        }

        $mail = Mail::to($data['to']);
        if (!empty($data['cc'])) {
            $mail->cc($data['cc']);
        }
        if (!empty($data['bcc'])) {
            $mail->bcc($data['bcc']);
        }

        // Igualamos el comportamiento del de facturas para no romper nada
        app()->environment('local') ? $mail->send($mailable) : $mail->queue($mailable);

        return back()->with('ok', 'Presupuesto enviado por email.');
    }
}