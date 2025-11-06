<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); // ya estás en middleware auth
    }

    public function rules(): array
    {
        return [
            'legal_name'    => ['nullable','string','max:190'],
            'tax_id'        => ['nullable','string','max:60'],
            'logo'          => ['nullable','image','mimes:png,jpg,jpeg,webp','max:4096'],
            'remove_logo'   => ['nullable','boolean'],

            'address'       => ['nullable','string','max:190'],
            'zip'           => ['nullable','string','max:20'],
            'city'          => ['nullable','string','max:120'],
            'country'       => ['nullable','string','size:2'],

            'currency_code' => ['required','string','size:3'],
            'locale'        => ['required','string','max:10'],
            'timezone'      => ['required','string','max:60'],

            'pdf_template'  => ['required','string','max:50'],
            'bank_account' => ['nullable','string','max:190'],
            'billing_notes'=> ['nullable','string'],

            // ⬇️ nuevos flags
            'show_bank_on_invoices' => ['sometimes','boolean'],
            'show_bank_on_budgets'  => ['sometimes','boolean'],
        ];
    }
}
