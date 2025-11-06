<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Presupuesto {{ $budget->number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/js/app.js'])
    <style>
        body {
            background: #f6f7f9;
        }

        .brand {
            color: #2fca6c;
            font-weight: 800;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-light bg-white shadow-sm">
        <div class="container-xxl d-flex align-items-center">
            <img src="{{ asset('logo_facturador.png') }}" alt="Facturador" style="height:26px" class="me-2">
            <span class="brand">Facturador</span>
            <span class="ms-auto text-muted small">Presupuesto público</span>
        </div>
    </nav>

    <main class="py-4">
        <div class="container-xxl">
            @if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif

            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-0">Presupuesto {{ $budget->number }}</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('budgets.pdf', $budget) }}" class="btn btn-outline-secondary" target="_blank">PDF</a>
                    @if($budget->status === 'sent' || $budget->status === 'draft')
                    <form class="d-inline" method="post" action="{{ route('public.budgets.accept',$budget->public_token) }}">
                        @csrf
                        <button class="btn btn-success">Aceptar</button>
                    </form>
                    <form class="d-inline" method="post" action="{{ route('public.budgets.reject',$budget->public_token) }}">
                        @csrf
                        <button class="btn btn-outline-danger">Rechazar</button>
                    </form>
                    @endif
                </div>
            </div>

            <div class="card card-soft mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div><strong>Cliente:</strong> {{ $budget->client->name }}</div>
                            @if($budget->client->email)<div><strong>Email:</strong> {{ $budget->client->email }}</div>@endif
                            @if($budget->client->tax_id)<div><strong>NIF/CIF:</strong> {{ $budget->client->tax_id }}</div>@endif
                        </div>
                        <div class="col-md-6">
                            <div><strong>Fecha:</strong> {{ optional($budget->date)->format('d/m/Y') }}</div>
                            <div><strong>Válido hasta:</strong> {{ optional($budget->due_date)->format('d/m/Y') ?? '—' }}</div>
                            <div><strong>Estado:</strong> <span class="badge bg-light text-dark">{{ ucfirst($budget->status) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-soft mb-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th class="text-end">Cant.</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end">Desc. %</th>
                                    <th class="text-end">IVA %</th>
                                    <th class="text-end">Total línea</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($budget->items as $it)
                                <tr>
                                    <td>{{ $it->description }}</td>
                                    <td class="text-end">{{ number_format($it->quantity,3,',','.') }}</td>
                                    <td class="text-end">{{ number_format($it->unit_price,2,',','.') }} €</td>
                                    <td class="text-end">{{ rtrim(rtrim((string)$it->discount,'0'),'.') }}</td>
                                    <td class="text-end">{{ rtrim(rtrim((string)$it->tax_rate,'0'),'.') }}</td>
                                    <td class="text-end">{{ number_format($it->total_line,2,',','.') }} €</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 text-end">
                        <div><strong>Subtotal:</strong> {{ number_format($budget->subtotal,2,',','.') }} €</div>
                        <div><strong>Impuestos:</strong> {{ number_format($budget->tax_total,2,',','.') }} €</div>
                        <div class="fs-5"><strong>Total:</strong> {{ number_format($budget->total,2,',','.') }} €</div>
                    </div>
                </div>
            </div>

            @if($budget->notes)
            <div class="card card-soft mb-3">
                <div class="card-body">
                    <div class="fw-bold mb-1">Notas</div>
                    <div class="text-muted">{{ $budget->notes }}</div>
                </div>
            </div>
            @endif

            @if($budget->terms)
            <div class="card card-soft mb-3">
                <div class="card-body">
                    <div class="fw-bold mb-1">Términos</div>
                    <div class="text-muted">{{ $budget->terms }}</div>
                </div>
            </div>
            @endif
        </div>
    </main>

    <footer class="py-4 bg-white border-top mt-4">
        <div class="container-xxl small text-muted">
            © {{ date('Y') }} Facturador — Visualización pública de presupuesto.
        </div>
    </footer>
</body>

</html>
