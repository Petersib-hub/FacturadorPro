@extends('layouts.app')
@section('title', 'Checklist de Cumplimiento (HAC/1177/2024)')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Checklist de Cumplimiento — Orden HAC/1177/2024</h4>
    <a href="{{ route('compliance.declaracion') }}" class="btn btn-sm btn-primary">Declaración responsable</a>
</div>

<div class="card card-soft">
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Artículo</th>
                    <th>Requisito</th>
                    <th class="text-center">Estado</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Art. 6</strong></td>
                    <td>Integridad e inalterabilidad (huellas encadenadas en eventos)</td>
                    <td class="text-center">
                        <span class="badge {{ ($status['art_6_integridad'] === 'OK') ? 'bg-success' : 'bg-warning text-dark' }}">{{ $status['art_6_integridad'] }}</span>
                    </td>
                    <td>Hash SHA-256 + prev_hash en compliance_event_logs.</td>
                </tr>
                <tr>
                    <td><strong>Art. 7</strong></td>
                    <td>Trazabilidad (encadenamiento + referencia de entidad)</td>
                    <td class="text-center">
                        <span class="badge {{ ($status['art_7_trazabilidad'] === 'OK') ? 'bg-success' : 'bg-warning text-dark' }}">{{ $status['art_7_trazabilidad'] }}</span>
                    </td>
                    <td>Encadenamiento cronológico y referencia a factura/presupuesto.</td>
                </tr>
                <tr>
                    <td><strong>Art. 8</strong></td>
                    <td>Conservación, accesibilidad y legibilidad</td>
                    <td class="text-center">
                        <span class="badge {{ ($status['art_8_conservacion'] === 'OK') ? 'bg-success' : 'bg-warning text-dark' }}">{{ $status['art_8_conservacion'] }}</span>
                    </td>
                    <td>Exportables por rango de fechas (ver comando de exportación).</td>
                </tr>
                <tr>
                    <td><strong>Art. 9</strong></td>
                    <td>Registro de eventos obligatorios del sistema</td>
                    <td class="text-center">
                        <span class="badge {{ ($status['art_9_registro'] === 'OK') ? 'bg-success' : 'bg-warning text-dark' }}">{{ $status['art_9_registro'] }}</span>
                    </td>
                    <td>NO VERI*FACTU inicio/fin, escaneo de anomalías, etc.</td>
                </tr>
                <tr>
                    <td><strong>Art. 13</strong></td>
                    <td>Huella de registros (hash)</td>
                    <td class="text-center">
                        <span class="badge {{ ($status['art_13_huella'] === 'OK') ? 'bg-success' : 'bg-warning text-dark' }}">{{ $status['art_13_huella'] }}</span>
                    </td>
                    <td>SHA-256 sobre contenido + prev_hash + timestamp.</td>
                </tr>
                <tr>
                    <td><strong>Art. 14</strong></td>
                    <td>Firma electrónica</td>
                    <td class="text-center">
                        <span class="badge bg-warning text-dark">{{ $status['art_14_firma'] }}</span>
                    </td>
                    <td>Firmar XML/JSON de registros con certificado (pendiente integrar).</td>
                </tr>
                <tr>
                    <td><strong>Art. 15</strong></td>
                    <td>Declaración responsable</td>
                    <td class="text-center">
                        <span class="badge {{ ($status['art_15_declaracion'] === 'OK') ? 'bg-success' : 'bg-warning text-dark' }}">{{ $status['art_15_declaracion'] }}</span>
                    </td>
                    <td>Disponible la vista y exportación a PDF.</td>
                </tr>
                <tr>
                    <td><strong>Art. 20–21</strong></td>
                    <td>Código QR y mención VERI*FACTU en factura</td>
                    <td class="text-center">
                        <span class="badge {{ ($status['art_20_21_qr'] === 'OK') ? 'bg-success' : 'bg-warning text-dark' }}">{{ $status['art_20_21_qr'] }}</span>
                    </td>
                    <td>Incluye parcial <code>verifactu._qr</code> en la ficha PDF/vista.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="small text-muted mt-2">
    Basado en la Orden HAC/1177/2024 (BOE-A-2024-22138). Este checklist es orientativo; la AEAT puede publicar detalles técnicos adicionales en su sede.
</div>
@endsection
