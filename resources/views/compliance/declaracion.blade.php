@extends('layouts.pdf') {{-- o layouts.app si prefieres verlo en pantalla --}}
@section('title','Declaración responsable del sistema informático de facturación')

@section('content')
<style>
    body { font-size: 13px; }
    h1 { font-size: 20px; margin-bottom: 0.5rem; }
    h2 { font-size: 16px; margin-top: 1rem; }
    .small { color: #666; font-size: 11px; }
    .box { border: 1px solid #ddd; padding: 12px; border-radius: 8px; }
</style>

<h1>DECLARACIÓN RESPONSABLE DEL SISTEMA INFORMÁTICO DE FACTURACIÓN</h1>
<p class="small">Orden HAC/1177/2024, de 17 de octubre — Artículo 15.</p>

@php
    $companyName = $company->company_name ?? 'Mi Empresa S.L.';
    $taxId = $company->company_tax_id ?? 'A00000000';
    $address = $company->company_address ?? 'Dirección fiscal';
    $city = $company->company_city ?? '';
    $zip = $company->company_zip ?? '';
    $province = $company->company_province ?? '';
    $country = $company->company_country ?? 'ES';
    $softwareName = config('app.name', 'FacturadorPro');
    $softwareVersion = config('app.version', 'v1.0.0');
    $producerName = 'Mi Empresa S.L.'; // productor del software
    $producerTaxId = 'A00000000';
    $declarantName = auth()->user()->name ?? 'Administrador';
    $declarantId = ''; // DNI/NIF del declarante (rellenar)
    $today = now()->format('d/m/Y');
@endphp

<div class="box">
    <h2>1. Datos del productor del sistema</h2>
    <p><strong>Productor (razón social):</strong> {{ $producerName }}</p>
    <p><strong>NIF:</strong> {{ $producerTaxId }}</p>
    <p><strong>Denominación del sistema:</strong> {{ $softwareName }}</p>
    <p><strong>Versión:</strong> {{ $softwareVersion }}</p>

    <h2>2. Datos del obligado tributario usuario</h2>
    <p><strong>Razón social / Nombre y apellidos:</strong> {{ $companyName }}</p>
    <p><strong>NIF:</strong> {{ $taxId }}</p>
    <p><strong>Domicilio fiscal:</strong> {{ $address }}, {{ $zip }} {{ $city }} ({{ $province }}) — {{ $country }}</p>

    <h2>3. Ámbito de uso del sistema</h2>
    <p>El sistema informático de facturación aquí declarado se utiliza para la emisión y, en su caso, remisión de registros de facturación conforme al Reglamento aprobado por RD 1007/2023 y a la Orden HAC/1177/2024. El sistema podrá operar como VERI*FACTU en los términos del capítulo V de la Orden.</p>

    <h2>4. Manifestaciones (contenido mínimo del art. 15)</h2>
    <ol>
        <li>Que el sistema garantiza la <strong>integridad e inalterabilidad</strong> de los registros (art. 6), mediante el encadenamiento con huellas (art. 13) y, en su caso, firma (art. 14).</li>
        <li>Que el sistema garantiza la <strong>trazabilidad</strong> de los registros (art. 7) y mantiene un <strong>registro de eventos</strong> (art. 9) conforme a los requisitos exigidos.</li>
        <li>Que se asegura la <strong>conservación, accesibilidad y legibilidad</strong> de los registros (art. 8) y su puesta a disposición a requerimiento (art. 18).</li>
        <li>Que las facturas incluyen el <strong>código QR</strong> y, en su caso, la mención VERI*FACTU (cap. VIII, arts. 20–21).</li>
        <li>Que se siguen los formatos y contenidos de registros exigidos (cap. III, arts. 10–11 y anexo).</li>
    </ol>

    <h2>5. Componentes y terceros (recomendado art. 15)</h2>
    <p>Listado de componentes y módulos integrados (productor / versión):</p>
    <ul>
        <li>Core de facturación — {{ $softwareName }} {{ $softwareVersion }} ({{ $producerName }})</li>
        <li>Módulo VERI*FACTU — {{ $softwareName }} {{ $softwareVersion }}</li>
        <li>PDF (dompdf) — versión instalada en composer.lock</li>
        <li>Base de datos — MySQL/MariaDB</li>
        <li>Otros: ________</li>
    </ul>

    <h2>6. Ubicación de esta declaración</h2>
    <p>Esta declaración se encuentra disponible en el propio sistema de facturación, en el apartado <em>Configuración → Cumplimiento</em>, y se entrega al usuario obligado tributario.</p>

    <h2>7. Firma</h2>
    <p>En {{ $city ?: '_____' }}, a {{ $today }}</p>
    <p>El declarante (productor / representante):</p>
    <p><strong>{{ $declarantName }}</strong> — DNI/NIF: {{ $declarantId ?: '_____' }}</p>
    <br><br>
    <p>Firma: ____________________________</p>
</div>

<div class="small mt-3">
    Nota: Plantilla basada en el contenido mínimo del art. 15 de la Orden HAC/1177/2024 (BOE-A-2024-22138).
</div>
@endsection
