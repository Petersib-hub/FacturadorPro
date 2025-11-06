<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización de instancia la resuelve la Policy (update)
        return true;
    }

    public function rules(): array
    {
        $userId   = $this->user()->id;
        $client   = $this->route('client');
        $clientId = $client?->id;

        return [
            'name'    => ['required','string','max:190'],
            'email'   => [
                'nullable','email','max:190',
                Rule::unique('clients','email')
                    ->ignore($clientId)
                    ->where(fn($q) => $q->where('user_id', $userId)),
            ],
            'phone'   => ['nullable','string','max:40'],
            'tax_id'  => [
                'nullable','string','max:40',
                Rule::unique('clients','tax_id')
                    ->ignore($clientId)
                    ->where(fn($q) => $q->where('user_id', $userId)),
            ],
            'address' => ['nullable','string','max:190'],
            'zip'     => ['nullable','string','max:20'],
            'city'    => ['nullable','string','max:120'],
            'country' => ['nullable','string','size:2'],
            // Al editar no exigimos consentimiento, pero si viene marcado lo usaremos
            'consent' => ['nullable','accepted'],
        ];
    }
}
