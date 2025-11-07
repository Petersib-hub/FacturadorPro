<?php

return [
    'enabled'           => env('VERIFACTU_ENABLED', false),
    'env'               => env('VERIFACTU_ENV', 'sandbox'), // sandbox|live
    'software_id'       => env('VERIFACTU_SOFTWARE_ID'),
    'software_name'     => env('VERIFACTU_SOFTWARE_NAME', 'FacturadorPro'),
    'software_version'  => env('VERIFACTU_SOFTWARE_VERSION', config('app.version', 'dev')),
    'tax_id'            => env('VERIFACTU_TAX_ID'), // NIF/CIF emisor
    'cert_path'         => env('VERIFACTU_CERT_PATH'), // ruta al .p12/.pem si aplica
    'cert_pass'         => env('VERIFACTU_CERT_PASS'), // password del certificado
    'api_base'          => env('VERIFACTU_API_BASE', 'https://api.sandbox.fiskaly.com'),
    'timeout'           => env('VERIFACTU_TIMEOUT', 15),
];
