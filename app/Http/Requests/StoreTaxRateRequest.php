<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaxRateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'       => ['required','string','max:255'],
            'rate'       => ['required','numeric','min:0','max:100'],
            'is_default' => ['sometimes','boolean'],
            'is_exempt'  => ['sometimes','boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_default' => (bool)$this->input('is_default', false),
            'is_exempt'  => (bool)$this->input('is_exempt', false),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            // Si es exenta, la tasa debe ser 0
            if ($this->boolean('is_exempt') && (float)$this->input('rate', 0) != 0.0) {
                $v->errors()->add('rate', 'Las tasas exentas deben tener 0.000%.');
            }
        });
    }
}
