<?php

return [
    'provider' => env('VERIFACTU_PROVIDER', 'fiskaly'),
    'qr' => [
        'enabled' => true,
        'size' => 180, // px
        'margin' => 2, // módulos
        'ecc' => 'M',  // L | M | Q | H
    ],
    'export' => [
        'path' => storage_path('app/verifactu/exports'),
    ],
];
