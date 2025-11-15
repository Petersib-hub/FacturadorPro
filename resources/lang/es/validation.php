<?php

return [
    'required'  => 'Este campo es obligatorio.',
    'email'     => 'Introduce un correo válido.',
    'confirmed' => 'La confirmación no coincide.',
    'min'       => [
        'string' => 'Debe tener al menos :min caracteres.',
    ],
    'password'  => [
        'letters' => 'Debe contener letras.',
        'mixed'   => 'Debe incluir mayúsculas y minúsculas.',
        'numbers' => 'Debe incluir números.',
    ],
];