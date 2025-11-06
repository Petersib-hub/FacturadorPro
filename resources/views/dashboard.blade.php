@extends('layouts.app')
@section('title','Dashboard')

@php use Illuminate\Support\Str; @endphp

@php
    // Avatar del usuario (bienvenida)
    $u = auth()->user();
    $avatarUrl = $u?->avatar_path
        ? asset('storage/'.$u->avatar_path)
        : 'https://ui-avatars.com/api/?name='.urlencode($u?->name ?? 'User').'&background=2fca6c&color=fff&size=128';

    // Mapa de estados → badge para listas rápidas
    $statusMap = [
        'draft'   => ['label'=>'Borrador','class'=>'bg-secondary'],
        'pending' => ['label'=>'Pendiente','class'=>'bg-warning text-dark'],
        'sent'    => ['label'=>'Enviada','class'=>'bg-info text-dark'],
        'paid'    => ['label'=>'Pagada','class'=>'bg-success'],
        'void'    => ['label'=>'Anulada','class'=>'bg-dark'],
        'accepted'=> ['label'=>'Aceptado','class'=>'bg-success'],
        'rejected'=> ['label'=>'Rechazado','class'=>'bg-danger'],
    ];
@endphp

@section('content')
<div class="row g-3">

    {{-- Tarjeta de bienvenida con avatar --}}
    <div class="col-12">
        <div class="card card-soft">
            <div class="card-body d-flex align-items-center gap-3">
                <img src="{{ $avatarUrl }}" alt="Avatar" class="rounded-circle" style="width:56px;height:56px;object-fit:cover;">
                <div>
                    <div class="fw-semibold">¡Hola, {{ $u?->name ?? 'Usuario' }}!</div>
                    <div class="text-muted small">Bienvenido a tu panel de control.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Métricas rápidas --}}
    <div class="col-md-4">
        <div class="card card-soft h-100">
            <div class="card-body">
                <div class="text-muted small">Facturado este mes</div>
                <div class="fs-4 fw-bold">{{ number_format($invoicedThisMonth,2,',','.') }} €</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-soft h-100">
            <div class="card-body">
                <div class="text-muted small">Pendiente de cobro</div>
                <div class="fs-4 fw-bold">{{ number_format($pendingAmount,2,',','.') }} €</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-soft h-100">
            <div class="card-body">
                <div class="text-muted small">Presupuestos abiertos</div>
                <div class="fs-4 fw-bold">{{ $budgetsOpen }}</div>
            </div>
        </div>
    </div>

    {{-- Facturas recientes --}}
    <div class="col-lg-7">
        <div class="card card-soft">
            <div class="card-body">
                <h6 class="mb-3">Facturas recientes</h6>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th class="text-end">Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentInvoices as $i)
                            @php
                                $s = $statusMap[$i->status] ?? ['label'=>ucfirst($i->status),'class'=>'bg-light text-dark'];
                            @endphp
                            <tr>
                                <td><a href="{{ route('invoices.show',$i) }}">{{ $i->number }}</a></td>
                                <td>{{ $i->client?->name }}</td>
                                <td>{{ optional($i->date)->format('d/m/Y') }}</td>
                                <td class="text-end">{{ number_format($i->total,2,',','.') }} €</td>
                                <td><span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Sin facturas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Top clientes (6 meses) con suma y nº de facturas --}}
    <div class="col-lg-5">
        <div class="card card-soft">
            <div class="card-body">
                <h6 class="mb-3">Top clientes (6 meses)</h6>

                <ul class="list-group list-group-flush">
                    @forelse($topClients as $row)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $row->client?->name ?? '—' }}</div>
                                <div class="small text-muted">
                                    {{-- Ojo: usar FQN para evitar el error de Str --}}
                                    {{ $row->count_invoices }}
                                    {{ Str::plural('factura', $row->count_invoices) }}

                                    en el periodo
                                </div>
                            </div>
                            <strong>{{ number_format($row->total_sum,2,',','.') }} €</strong>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Sin datos.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Presupuestos recientes --}}
    <div class="col-12">
        <div class="card card-soft">
            <div class="card-body">
                <h6 class="mb-3">Presupuestos recientes</h6>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th class="text-end">Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBudgets as $b)
                            @php
                                $s = $statusMap[$b->status] ?? ['label'=>ucfirst($b->status),'class'=>'bg-light text-dark'];
                            @endphp
                            <tr>
                                <td><a href="{{ route('budgets.show',$b) }}">{{ $b->number }}</a></td>
                                <td>{{ $b->client?->name }}</td>
                                <td>{{ optional($b->date)->format('d/m/Y') }}</td>
                                <td class="text-end">{{ number_format($b->total,2,',','.') }} €</td>
                                <td><span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Sin presupuestos.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
