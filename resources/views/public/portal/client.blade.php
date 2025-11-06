<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Portal de {{ $client->name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f6f7f9;
        }

        .card-soft {
            border: 1px solid #eef1f5;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
        }

        .brand {
            color: #2fca6c;
            font-weight: 800;
        }

        .navbar-brand img {
            height: 28px;
        }

        .nav-pills .nav-link.active {
            background: #2fca6c;
        }
    </style>
</head>

<body>
        @php
            $tenantLogo = \App\Support\Branding::tenantLogoUrl($client->user_id);
        @endphp

        <nav class="navbar navbar-light bg-white border-bottom mb-3">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                    <img src="{{ $tenantLogo }}" alt="Logo" style="height:28px;">
                    <span class="brand">Portal del cliente</span>
                </a>
                <div class="small text-muted">{{ config('app.name') }}</div>
            </div>
        </nav>


    <div class="container mb-5">

        @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="mb-0">Hola, {{ $client->name }}</h4>
            <form class="d-flex" method="get">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text" name="q" class="form-control" placeholder="Buscar por número..."
                    value="{{ $q }}">
                <button class="btn btn-outline-secondary ms-2">Buscar</button>
            </form>
        </div>

        <ul class="nav nav-pills mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $tab==='invoices' ? 'active' : '' }}"
                    href="{{ request()->fullUrlWithQuery(['tab'=>'invoices','q'=>$q,'pi'=>null,'pb'=>null]) }}">
                    Facturas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='budgets' ? 'active' : '' }}"
                    href="{{ request()->fullUrlWithQuery(['tab'=>'budgets','q'=>$q,'pi'=>null,'pb'=>null]) }}">
                    Presupuestos
                </a>
            </li>
        </ul>

        {{-- FACTURAS --}}
        @if($tab==='invoices')
        <div class="card card-soft">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Número</th>
                                <th>Fecha</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $inv)
                            <tr>
                                <td>{{ $inv->number }}</td>
                                <td>{{ optional($inv->date)->format('d/m/Y') }}</td>
                                <td class="text-end">{{ number_format($inv->total,2,',','.') }} {{ $inv->currency }}</td>
                                <td class="text-center text-capitalize">{{ $inv->status }}</td>
                                <td class="text-end">
                                    {{-- Enlace público a la factura por token ya lo tienes en public.invoices.show --}}
                                    <a class="btn btn-sm btn-outline-secondary"
                                        href="{{ route('public.invoices.show', $inv->public_token) }}" target="_blank" rel="noopener">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No hay facturas para mostrar.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-2">
                    {{ $invoices->withQueryString()->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
        @endif

        {{-- PRESUPUESTOS --}}
        @if($tab==='budgets')
        <div class="card card-soft">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Número</th>
                                <th>Fecha</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($budgets as $b)
                            <tr>
                                <td>{{ $b->number }}</td>
                                <td>{{ optional($b->date)->format('d/m/Y') }}</td>
                                <td class="text-end">{{ number_format($b->total,2,',','.') }} {{ $b->currency }}</td>
                                <td class="text-center text-capitalize">{{ $b->status }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary"
                                        href="{{ route('public.budgets.show', $b->public_token) }}" target="_blank" rel="noopener">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No hay presupuestos para mostrar.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-2">
                    {{ $budgets->withQueryString()->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
        @endif

    </div>

    <footer class="text-center text-muted small py-4">
        © {{ date('Y') }} {{ config('app.name') }}
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
