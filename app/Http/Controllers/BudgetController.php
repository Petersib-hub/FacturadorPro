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

        return view('budgets.index', compact('budgets', 'q'));
    }

    public function create()
    {
        $clients = Client::query()->where('user_id', auth()->id())->orderBy('name')->get();
        $products = Product::query()->where('user_id', auth()->id())->orderBy('name')->get();
        $taxRates = TaxRate::forAuthUser()->orderByDesc('is_default')->orderBy('rate')->get();

        return view('budgets.create', compact('clients', 'products', 'taxRates'));
    }

    public function store(StoreBudgetRequest $request)
    {
        $v = $request->validated();

        $v['currency'] = $v['currency'] ?? 'EUR';
        $v['status']   = 'draft';
        $v['date']     = $v['date'] ?? now()->toDateString();
        $v['due_date'] = $v['due_date'] ?? now()->addDays(14)->toDateString();

        unset($v['number']); // nunca aceptar del form

        return DB::transaction(function () use ($v) {
            $attempts = 0;
            $series   = 'PRES';
            $year     = (int) date('Y', strtotime($v['date']));

            retry_create:
            try {
                $number = NumberSequence::next('budget', $series, $year); // PRES-YYYY-####
                $sequence = 0;
                if (preg_match('/^PRES-(\d{4})-(\d{4})$/', $number, $m)) {
                    $year     = (int)($m[1] ?? $year);
                    $sequence = (int)($m[2] ?? 0);
                }

                $budget = Budget::create([
                    'user_id'      => auth()->id(),
                    'client_id'    => $v['client_id'],
                    'number'       => $number,
                    'sequence'     => $sequence,
                    'year'         => $year,
                    'date'         => $v['date'],
                    'due_date'     => $v['due_date'],
                    'currency'     => $v['currency'],
                    'status'       => 'draft',
                    'notes'        => $v['notes'] ?? null,
                    'terms'        => $v['terms'] ?? null,
                    'public_token' => Str::random(48),
                ]);
            } catch (UniqueConstraintViolationException $e) {
                if ($attempts++ < 3) goto retry_create;
                throw $e;
            }

            $subtotal = 0; $tax_total = 0; $total = 0; $pos = 0;

            foreach ($v['items'] as $it) {
                $qty   = (float)$it['quantity'];
                $price = (float)$it['unit_price'];
                $rate  = (float)$it['tax_rate'];
                $disc  = (float)($it['discount'] ?? 0);

                $base = $qty * $price;
                $d = $base * ($disc / 100);
                $after = $base - $d;
                $tax = $after * ($rate / 100);
                $line = $after + $tax;

                $subtotal += $after; $tax_total += $tax; $total += $line;

                BudgetItem::create([
                    'budget_id'   => $budget->id,
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

            $budget->update(compact('subtotal', 'tax_total', 'total'));

            return redirect()->route('budgets.show', $budget)->with('ok', 'Presupuesto creado.');
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

        $clients = Client::query()->where('user_id', auth()->id())->orderBy('name')->get();
        $products = Product::query()->where('user_id', auth()->id())->orderBy('name')->get();
        $taxRates = TaxRate::forAuthUser()->orderByDesc('is_default')->orderBy('rate')->get();

        return view('budgets.edit', compact('budget', 'clients', 'products', 'taxRates'));
    }

    public function update(UpdateBudgetRequest $request, Budget $budget)
    {
        $v = $request->validated();

        return DB::transaction(function () use ($v, $budget) {
            $budget->update([
                'client_id' => $v['client_id'],
                'date'      => $v['date'] ?? $budget->date,
                'due_date'  => $v['due_date'] ?? ($budget->due_date ?? now()->addDays(14)->toDateString()),
                'currency'  => $v['currency'] ?? 'EUR',
                'notes'     => $v['notes'] ?? null,
                'terms'     => $v['terms'] ?? null,
            ]);

            $budget->items()->delete();

            $subtotal = 0; $tax_total = 0; $total = 0; $pos = 0;

            foreach ($v['items'] as $it) {
                $qty   = (float)$it['quantity'];
                $price = (float)$it['unit_price'];
                $rate  = (float)$it['tax_rate'];
                $disc  = (float)($it['discount'] ?? 0);

                $base = $qty * $price;
                $d = $base * ($disc / 100);
                $after = $base - $d;
                $tax = $after * ($rate / 100);
                $line = $after + $tax;

                $subtotal += $after; $tax_total += $tax; $total += $line;

                BudgetItem::create([
                    'budget_id'   => $budget->id,
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

            $budget->update(compact('subtotal', 'tax_total', 'total'));

            return redirect()->route('budgets.show', $budget)->with('ok', 'Presupuesto actualizado.');
        });
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        return redirect()->route('budgets.index')->with('ok', 'Presupuesto eliminado.');
    }

    public function pdf(Budget $budget)
    {
        $budget->load('client', 'items');

        $view = \App\Support\PdfTemplates::budgetView($budget->user_id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, compact('budget'));
        return $pdf->download($budget->number . '.pdf');
    }

    public function email(Request $request, Budget $budget)
    {
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

        $view = \App\Support\PdfTemplates::budgetView($budget->user_id);
        $pdf = Pdf::loadView($view, compact('budget', 'company'))->output();

        $mailable = new BudgetMail($budget, $pdf);
        $mailable->subject($data['subject'] ?? ('Presupuesto ' . $budget->number));

        $mail = Mail::to($data['to']);
        if (!empty($data['cc']))  $mail->cc($data['cc']);
        if (!empty($data['bcc'])) $mail->bcc($data['bcc']);

        app()->environment('local') ? $mail->send($mailable) : $mail->queue($mailable);

        return back()->with('ok', 'Presupuesto enviado por email.');
    }
}