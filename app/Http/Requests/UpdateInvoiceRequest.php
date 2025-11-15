<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La Policy controla update; aquí no bloqueamos.
        return true;
    }

    public function rules(): array
    {
        return (new StoreInvoiceRequest())->rules();
    }
}