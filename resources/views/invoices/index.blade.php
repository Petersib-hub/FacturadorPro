@extends('layouts.app')
@section('title', 'Facturas')

@push('head')
<style>
    .table-responsive { overflow: visible; }
    .actions-col { width: 1%; white-space: nowrap; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Facturas</h4>
    <a href="{{ route('invoices.create') }}" class="btn btn-brand">Nueva factura</a>
</div>

<form class="input-group mb-3" method="get">
    <input name="q" class="form-control" placeholder="Buscar por número o cliente" value="{{ $q ?? '' }}">
    <button class="btn btn-outline-secondary">Buscar</button>
</form>

<div class="table-responsive card card-soft">
    <table class="table mb-0 align-middle">
        <thead>
            <tr>
                <th>Número</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th class="text-end">Total</th>
                <th class="text-end">Pagado</th>
                <th>Estado</th>
                <th class="text-end actions-col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @php
                $statusMap = [
                    'draft'   => ['label' => 'Borrador', 'class' => 'bg-secondary'],
                    'pending' => ['label' => 'Pendiente', 'class' => 'bg-warning text-dark'],
                    'sent'    => ['label' => 'Enviada', 'class' => 'bg-info text-dark'],
                    'paid'    => ['label' => 'Pagada', 'class' => 'bg-success'],
                    'void'    => ['label' => 'Anulada', 'class' => 'bg-dark'],
                ];
            @endphp

            @forelse($invoices as $i)
                @php
                    $sm = $statusMap[$i->status] ?? ['label' => ucfirst($i->status), 'class' => 'bg-light text-dark'];
                    $publicUrl = $i->public_token ? route('public.invoices.show', $i->public_token) : null;
                @endphp
                <tr>
                    <td><a href="{{ route('invoices.show', $i) }}">{{ $i->number }}</a></td>
                    <td>{{ $i->client?->name }}</td>
                    <td>{{ optional($i->date)->format('d/m/Y') }}</td>
                    <td class="text-end">{{ number_format($i->total, 2, ',', '.') }} €</td>
                    <td class="text-end">{{ number_format($i->amount_paid, 2, ',', '.') }} €</td>
                    <td><span class="badge {{ $sm['class'] }}">{{ $sm['label'] }}</span></td>
                    <td class="text-end">
                        <div class="btn-group">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('invoices.show', $i) }}">Ver</a>
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Más</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('invoices.edit', $i) }}">Editar</a></li>
                                <li><a class="dropdown-item" href="{{ route('invoices.pdf', $i) }}" target="_blank" rel="noopener">Descargar PDF</a></li>
                                <li><a class="dropdown-item" href="{{ route('invoices.show', $i) }}#sendEmailModal" data-bs-toggle="modal" data-bs-target="#sendEmailModal-{{ $i->id }}">Enviar por email</a></li>
                                <li>
                                    <form method="post" action="{{ route('invoices.markSent', $i) }}" onsubmit="return confirm('¿Marcar como enviada?')">
                                        @csrf
                                        <button class="dropdown-item" type="submit">Marcar como enviada</button>
                                    </form>
                                </li>
                                @if($publicUrl)
                                    <li><a class="dropdown-item" href="#" onclick="copyPublicLink('{{ $publicUrl }}', {{ $i->id }})">Copiar enlace público</a></li>
                                    <li><a class="dropdown-item" href="{{ $publicUrl }}" target="_blank" rel="noopener">Abrir portal del cliente</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form class="d-inline" method="post" action="{{ route('invoices.destroy', $i) }}" onsubmit="return confirm('¿Eliminar factura?')">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger" type="submit">Eliminar</button>
                                    </form>
                                </li>
                            </ul>
                        </div>

                        <div class="modal fade" id="sendEmailModal-{{ $i->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form class="modal-content" method="post" action="{{ route('invoices.email', $i) }}">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Enviar {{ $i->number }} por email</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label">Para</label>
                                            <input type="email" name="to" class="form-control" required value="{{ $i->client->email ?? '' }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Asunto</label>
                                            <input type="text" name="subject" class="form-control" maxlength="190" placeholder="Factura {{ $i->number }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">CC</label>
                                            <input type="email" name="cc" class="form-control">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">BCC</label>
                                            <input type="email" name="bcc" class="form-control">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button class="btn btn-brand">Enviar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aún no hay facturas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $invoices->links() }}
</div>
@endsection

@push('scripts')
<script>
async function copyPublicLink(url, invoiceId) {
    try {
        const token = document.querySelector('meta[name="csrf-token"]').content;
        let finalUrl = url;
        try {
            const resp = await fetch(`/invoices/${invoiceId}/link-copied`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
            });
            if (resp.ok) {
                const data = await resp.json();
                if (data?.url) finalUrl = data.url;
            }
        } catch (_) {}
        await navigator.clipboard.writeText(finalUrl);
        const n = document.createElement('div');
        n.className = 'alert alert-success mt-2';
        n.textContent = 'Enlace copiado al portapapeles.';
        (document.querySelector('.container-xxl') ?? document.body).prepend(n);
        setTimeout(() => n.remove(), 2000);
    } catch (e) {
        alert('No se pudo copiar el enlace.');
    }
}
</script>
@endpush
