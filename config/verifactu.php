<?php

return [
    'provider' => env('VERIFACTU_PROVIDER', 'fiskaly'),
    'qr' => [
        'enabled' => true,
    ],
    'export' => [
        'path' => storage_path('app/verifactu/exports'),
    ],
];
