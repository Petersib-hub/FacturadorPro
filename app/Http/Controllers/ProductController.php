<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\TaxRate;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        // Usa la policy de Product
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(Request $request)
    {
        $q = $request->get('q');

        $products = Product::query()
            ->where('user_id', $request->user()->id) // aislamos por usuario
            ->when($q, function ($b) use ($q) {
                $b->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('products.index', compact('products', 'q'));
    }

    public function create()
    {
        $taxRates = TaxRate::forAuthUser()
            ->orderByDesc('is_default')
            ->orderBy('rate')
            ->get();

        return view('products.create', compact('taxRates'));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id; // 👈 importante

        // Si viene tax_rate_id, tomamos su valor de rate como snapshot
        if (!empty($data['tax_rate_id'])) {
            $tr = TaxRate::forAuthUser()->find($data['tax_rate_id']);
            $data['tax_rate'] = $tr?->rate ?? 0;
        } else {
            $data['tax_rate'] = $data['tax_rate'] ?? 0;
        }

        Product::create($data);

        return redirect()
            ->route('products.index')
            ->with('ok', 'Producto creado correctamente.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $taxRates = TaxRate::forAuthUser()
            ->orderByDesc('is_default')
            ->orderBy('rate')
            ->get();

        return view('products.edit', compact('product', 'taxRates'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        // Nunca reasignamos user_id desde el request
        unset($data['user_id']);

        if (!empty($data['tax_rate_id'])) {
            $tr = TaxRate::forAuthUser()->find($data['tax_rate_id']);
            $data['tax_rate'] = $tr?->rate ?? 0;
        } else {
            $data['tax_rate'] = $data['tax_rate'] ?? 0;
        }

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('ok', 'Producto actualizado.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('ok', 'Producto eliminado.');
    }
}
