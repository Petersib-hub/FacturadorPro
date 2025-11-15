<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Dejamos la autorización a la Policy del controlador (authorizeResource).
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required','exists:clients,id'],
            'date'      => ['nullable','date'],
            'due_date'  => ['nullable','date','after_or_equal:date'],
            'currency'  => ['required','string','size:3'],
            'notes'     => ['nullable','string','max:5000'],
            'terms'     => ['nullable','string','max:5000'],

            'items'                => ['required','array','min:1'],
            'items.*.description'  => ['required','string','max:500'],
            'items.*.quantity'     => ['required','numeric','min:0.001'],
            'items.*.unit_price'   => ['required','numeric','min:0'],
            'items.*.tax_rate'     => ['required','numeric','min:0','max:999.999'],
            'items.*.discount'     => ['nullable','numeric','min:0','max:100'],
        ];
    }
}