<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoicePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Si tienes policy con habilidad 'pay', úsala; si no, deja true.
        $invoice = $this->route('invoice');
        return $this->user() ? $this->user()->can('pay', $invoice) : false;
    }

    /**
     * Normaliza amount: admite "1.012,17", "1,012.17", "€ 1.012,17", etc.
     */
    protected function prepareForValidation(): void
    {
        $raw = $this->input('amount') ?? $this->input('amount_display');

        if (is_string($raw)) {
            // deja sólo dígitos, coma, punto y signo
            $val = preg_replace('/[^\d,.\-]/', '', $raw);

            // Caso con ambos separadores → asumimos que el punto es miles y la coma decimal (es-ES)
            if (str_contains($val, '.') && str_contains($val, ',')) {
                // Elimina puntos de miles y cambia coma a punto decimal
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
            } else {
                // Sólo coma → úsala como decimal
                if (str_contains($val, ',') && !str_contains($val, '.')) {
                    $val = str_replace(',', '.', $val);
                } else {
                    // Sólo puntos: ya es decimal en notación en-US
                    // (nada que hacer)
                }
            }
        } else {
            $val = $raw;
        }

        $this->merge([
            'amount' => is_numeric($val) ? number_format((float)$val, 2, '.', '') : $val,
        ]);
    }

    public function rules(): array
    {
        return [
            'amount'       => ['required','numeric','min:0.01'],
            'payment_date' => ['required','date'],
            'method'       => ['required','in:bank_transfer,cash,card,other'],
            'notes'        => ['nullable','string','max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'El importe es obligatorio.',
            'amount.numeric'  => 'El importe no es válido.',
            'amount.min'      => 'El importe debe ser mayor que 0.',
        ];
    }
}
