<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización la hace la Policy via authorizeResource()
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name'    => ['required','string','max:190'],
            'email'   => [
                'nullable','email','max:190',
                Rule::unique('clients','email')->where(fn($q) => $q->where('user_id', $userId)),
            ],
            'phone'   => ['nullable','string','max:40'],
            'tax_id'  => [
                'nullable','string','max:40',
                Rule::unique('clients','tax_id')->where(fn($q) => $q->where('user_id', $userId)),
            ],
            'address' => ['nullable','string','max:190'],
            'zip'     => ['nullable','string','max:20'],
            'city'    => ['nullable','string','max:120'],
            'country' => ['nullable','string','size:2'],
            // RGPD — obligatorio al crear
            'consent' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent.accepted' => 'Debes aceptar el consentimiento de tratamiento de datos (LOPDGDD y RGPD).',
        ];
    }
}
