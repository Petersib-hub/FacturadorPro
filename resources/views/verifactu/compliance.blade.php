@php($statusMsg = session('status'))
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Veri*factu - Cumplimiento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="h3 mb-3">Panel de Cumplimiento Veri*factu</h1>

    @if($statusMsg)
        <div class="alert alert-success">{{ $statusMsg }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-2" method="POST" action="{{ route('verifactu.web.export') }}">
                @csrf
                <div class="col-auto">
                    <label class="form-label">Exportar periodo</label>
                    <input type="month" name="period" class="form-control" value="{{ now()->format('Y-m') }}">
                </div>
                <div class="col-auto align-self-end">
                    <button class="btn btn-outline-primary">Generar Export</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Últimas facturas</h2>
            </div>

            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Serie/Número</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>{{ $inv->id }}</td>
                            <td>{{ ($inv->series ?? '') }}-{{ ($inv->number ?? '') }}</td>
                            <td>{{ optional($inv->issue_date)->format('Y-m-d') ?? $inv->issue_date }}</td>
                            <td>{{ number_format((float)($inv->total ?? 0), 2, ',', '.') }} €</td>
                            <td>
                                @php($st = $inv->verifactu_status ?? 'draft')
                                <span class="badge text-bg-{{ $st === 'verified' ? 'success' : ($st === 'failed' ? 'danger' : ($st === 'pending' ? 'warning' : 'secondary')) }}">
                                    {{ ucfirst($st) }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('verifactu.web.verify', $inv) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-primary">Verificar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Sin facturas recientes.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
