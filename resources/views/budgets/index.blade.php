@extends('layouts.app')
@section('title','Presupuestos')

@push('head')
<style>
    .table-responsive { overflow: visible; }
    .actions-col { width: 1%; white-space: nowrap; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Presupuestos</h4>
    <a href="{{ route('budgets.create') }}" class="btn btn-brand">Nuevo presupuesto</a>
</div>

@if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif

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
                <th>Estado</th>
                <th class="text-end actions-col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @php
            $statusMap = [
                'draft'    => ['label'=>'Borrador',  'class'=>'bg-secondary'],
                'sent'     => ['label'=>'Enviado',   'class'=>'bg-info text-dark'],
                'accepted' => ['label'=>'Aceptado',  'class'=>'bg-success'],
                'rejected' => ['label'=>'Rechazado', 'class'=>'bg-danger'],
                'expired'  => ['label'=>'Vencido',   'class'=>'bg-dark'],
            ];
            @endphp

            @forelse($budgets as $b)
            @php
                $sm = $statusMap[$b->status] ?? ['label'=>ucfirst($b->status),'class'=>'bg-light text-dark'];
                $publicUrl = $b->public_token ? route('public.budgets.show', $b->public_token) : null;
            @endphp
            <tr>
                <td><a href="{{ route('budgets.show',$b) }}">{{ $b->number }}</a></td>
                <td>{{ $b->client?->name }}</td>
                <td>{{ optional($b->date)->format('d/m/Y') }}</td>
                <td class="text-end">{{ number_format($b->total,2,',','.') }} €</td>
                <td><span class="badge {{ $sm['class'] }}">{{ $sm['label'] }}</span></td>
                <td class="text-end">
                    <div class="btn-group">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('budgets.show',$b) }}">Ver</a>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="visually-hidden">Más</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('budgets.edit',$b) }}">Editar</a></li>
                            <li><a class="dropdown-item" href="{{ route('budgets.pdf',$b) }}" target="_blank" rel="noopener">Descargar PDF</a></li>
                            <li>
                                <form method="post" action="{{ route('budgets.convert',$b) }}" onsubmit="return confirm('¿Convertir a factura?')">
                                    @csrf
                                    <button class="dropdown-item" type="submit">Convertir a factura</button>
                                </form>
                            </li>
                            @if($publicUrl)
                                <li><a class="dropdown-item" href="#" onclick="copyBudgetLink('{{ $publicUrl }}')">Copiar enlace público</a></li>
                                <li><a class="dropdown-item" href="{{ $publicUrl }}" target="_blank" rel="noopener">Abrir enlace público</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form class="d-inline" method="post" action="{{ route('budgets.destroy',$b) }}" onsubmit="return confirm('¿Eliminar presupuesto?')">
                                    @csrf @method('DELETE')
                                    <button class="dropdown-item text-danger" type="submit">Eliminar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">Aún no hay presupuestos.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $budgets->links() }}
</div>
@endsection

@push('scripts')
<script>
    async function copyBudgetLink(url) {
        try {
            await navigator.clipboard.writeText(url);
            const n = document.createElement('div');
            n.className = 'alert alert-success mt-2';
            n.textContent = 'Enlace copiado al portapapeles.';
            (document.querySelector('.container-xxl') ?? document.body).prepend(n);
            setTimeout(()=>n.remove(),2000);
        } catch(e) {
            alert('No se pudo copiar el enlace.');
        }
    }
</script>
@endpush
