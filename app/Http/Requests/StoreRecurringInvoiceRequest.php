<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecurringInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'client_id'     => ['required','exists:clients,id'],
            'frequency'     => ['required', Rule::in(['monthly','quarterly','yearly'])],
            'start_date'    => ['nullable','date'],
            'next_run_date' => ['required','date'],
            'currency'      => ['required','string','size:3'],
            'public_notes'  => ['nullable','string','max:255'],
            'terms'         => ['nullable','string'],

            'items'                   => ['required','array','min:1'],
            'items.*.description'     => ['required','string','max:255'],
            'items.*.quantity'        => ['required','numeric','min:0.001'],
            'items.*.unit_price'      => ['required','numeric','min:0'],
            'items.*.tax_rate'        => ['required','numeric','min:0'],
            'items.*.discount'        => ['nullable','numeric','min:0','max:100'],
            'items.*.product_id'      => ['nullable','exists:products,id'],
            'items.*.position'        => ['nullable','integer','min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'currency' => strtoupper($this->input('currency','EUR')),
        ]);
    }
}
