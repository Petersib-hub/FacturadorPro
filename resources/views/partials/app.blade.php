<!doctype html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','Facturador')</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#2fca6c">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.js'])

    @stack('head')
    <style>
        body {
            background: var(--bg-soft);
        }

        .avatar-sm {
            width: 24px;
            height: 24px;
            border-radius: 9999px;
            object-fit: cover;
        }

        .avatar-lg {
            width: 32px;
            height: 32px;
            border-radius: 9999px;
            object-fit: cover;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container-xxl">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <img src="{{ \App\Support\Branding::appLogoUrl() }}" alt="App" style="height:28px" class="me-2">
                <span class="fw-bold">Facturador</span>
            </a>

            {{-- Botón menú (hamburger) --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                            href="{{ route('dashboard') }}">Dashboard</a>
                    </li>

                    @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}"
                            href="{{ route('clients.index') }}">Clientes</a>
                    </li>
                    <!--<li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
                            href="{{ route('products.index') }}">Productos</a>
                    </li>-->

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('budgets.*') ? 'active' : '' }}"
                            href="{{ route('budgets.index') }}">Presupuestos</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}"
                            href="{{ route('invoices.index') }}">Facturas</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('recurring-invoices.*') ? 'active' : '' }}"
                            href="{{ route('recurring-invoices.index') }}">
                            Recurrentes
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tax-rates.*') ? 'active' : '' }}"
                            href="{{ route('tax-rates.index') }}">
                            Impuestos
                        </a>
                    </li>

                    {{-- Perfil con avatar --}}
                    @php
                    $u = auth()->user();
                    $avatarUrl = $u?->avatar_path
                    ? asset('storage/'.$u->avatar_path)
                    : 'https://ui-avatars.com/api/?name='.urlencode($u?->name ?? 'U').'&background=2fca6c&color=fff&size=64';
                    @endphp
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ $avatarUrl }}" alt="Avatar" class="avatar-sm me-2" loading="lazy">
                            <span>{{ $u->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="px-3 py-2 d-flex align-items-center">
                                <img src="{{ $avatarUrl }}" alt="Avatar" class="avatar-lg me-2" loading="lazy">
                                <div class="small">
                                    <div class="fw-semibold">{{ $u->name }}</div>
                                    <div class="text-muted">{{ $u->email }}</div>
                                </div>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="{{ route('settings.edit') }}">Ajustes del negocio</a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item">Salir</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="btn btn-sm btn-brand ms-lg-3" href="{{ route('login') }}">Acceder</a>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-fill py-4">
        <div class="container-xxl">

            {{-- Mensajes flash / validación --}}
            @if(session('ok'))
            <div class="alert alert-success">{{ session('ok') }}</div>
            @endif
            @if($errors->any())
            <div class="alert alert-danger">
                <strong>Revisa los errores:</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="mt-auto py-4 bg-white border-top">
        <div class="container-xxl d-flex flex-column flex-md-row align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <img src="/logo_facturador.png" alt="Facturador" style="height:22px" class="me-2">
                <span class="small text-muted">© {{ date('Y') }} Facturador — Tu contabilidad, simplificada.</span>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="#" class="text-muted small me-3">Política de privacidad</a>
                <a href="#" class="text-muted small me-3">Cookies</a>
                <button id="btnInstall" class="btn btn-sm btn-outline-success d-none">Instalar app</button>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
