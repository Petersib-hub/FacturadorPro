<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaxRateRequest;
use App\Http\Requests\UpdateTaxRateRequest;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxRateController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(TaxRate::class, 'tax_rate');
    }

    public function index(Request $request)
    {
        $q = $request->get('q');
        $list = TaxRate::forAuthUser()
            ->when($q, fn($b)=>$b->where('name','like',"%{$q}%"))
            ->orderByDesc('is_default')
            ->orderBy('rate')
            ->paginate(12)->withQueryString();

        return view('tax_rates.index', compact('list','q'));
    }

    public function create()
    {
        return view('tax_rates.create');
    }

    public function store(StoreTaxRateRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        DB::transaction(function () use ($data) {
            // Si marcan como default, desmarcamos el resto
            if (!empty($data['is_default'])) {
                TaxRate::forAuthUser()->update(['is_default' => false]);
            }
            // Si es exenta, la tasa es 0 por consistencia
            if (!empty($data['is_exempt'])) {
                $data['rate'] = 0;
            }
            TaxRate::create($data);
        });

        return redirect()->route('tax-rates.index')->with('ok','Tasa creada correctamente.');
    }

    public function show(TaxRate $tax_rate)
    {
        return view('tax_rates.show', compact('tax_rate'));
    }

    public function edit(TaxRate $tax_rate)
    {
        return view('tax_rates.edit', compact('tax_rate'));
    }

    public function update(UpdateTaxRateRequest $request, TaxRate $tax_rate)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $tax_rate) {
            if (!empty($data['is_default'])) {
                TaxRate::forAuthUser()->where('id','<>',$tax_rate->id)->update(['is_default' => false]);
            }
            if (!empty($data['is_exempt'])) {
                $data['rate'] = 0;
            }
            $tax_rate->update($data);
        });

        return redirect()->route('tax-rates.index')->with('ok','Tasa actualizada.');
    }

    public function destroy(TaxRate $tax_rate)
    {
        $tax_rate->delete();
        return redirect()->route('tax-rates.index')->with('ok','Tasa eliminada.');
    }
}
