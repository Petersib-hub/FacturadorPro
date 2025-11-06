<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Usa la policy estándar: update sobre el recurso
        /** @var \App\Models\Product $product */
        $product = $this->route('product');
        return $this->user()->can('update', $product);
    }

    public function rules(): array
    {
        $userId    = $this->user()->id;
        /** @var \App\Models\Product|null $product */
        $product   = $this->route('product');
        $productId = $product?->id;

        return [
            'name'        => [
                'required', 'string', 'max:190',
                Rule::unique('products', 'name')
                    ->ignore($productId)
                    ->where(fn ($q) => $q->where('user_id', $userId)),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'unit_price'  => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
            'tax_rate'    => ['nullable', 'numeric', 'min:0', 'max:999.999'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'unit_price.required' => 'El precio unitario es obligatorio.',
        ];
    }
}
