<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('invoice.update');
    }

    public function rules(): array
    {
        return (new StoreInvoiceRequest())->rules();
    }
}
