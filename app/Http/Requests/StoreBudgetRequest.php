<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización la maneja BudgetPolicy + authorizeResource()
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            // El cliente debe existir y ser del usuario autenticado
            'client_id'        => [
                'required',
                Rule::exists('clients','id')->where(fn($q) => $q->where('user_id', $userId)),
            ],

            'date'             => ['nullable','date'],
            'due_date'         => ['nullable','date','after_or_equal:date'],

            'currency'         => ['required','string','size:3'],
            'notes'            => ['nullable','string','max:5000'],
            'terms'            => ['nullable','string','max:5000'],

            // Items
            'items'                => ['required','array','min:1'],
            'items.*.description'  => ['required','string','max:500'],
            'items.*.quantity'     => ['required','numeric','min:0.001'],
            'items.*.unit_price'   => ['required','numeric','min:0'],
            'items.*.tax_rate'     => ['required','numeric','min:0','max:999.999'],
            'items.*.discount'     => ['nullable','numeric','min:0','max:100'],

            // Si envías product_id en líneas, no lo obligo; pero si viene, que exista y sea del tenant
            'items.*.product_id'   => [
                'nullable',
                Rule::exists('products','id')->where(fn($q) => $q->where('user_id', $userId)),
            ],
        ];
    }
}
