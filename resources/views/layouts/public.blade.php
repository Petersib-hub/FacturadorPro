<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title','Documento')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CDN Bootstrap (evita Vite en portal público) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ background:#f6f7fb; color:#1f2937; }
        .container-paper{ max-width: 1100px; }
        .card-soft{ border:0; border-radius:16px; box-shadow:0 6px 24px rgba(0,0,0,.06); }
        .print-actions{ position:sticky; top:0; z-index:10; background:linear-gradient(#f6f7fb,#f6f7fbcc); padding:.75rem; }
        /* celdas de descripción con wrap correcto */
        .desc-cell{ white-space:pre-wrap; word-break:break-word; overflow-wrap:anywhere; }
    </style>
    @stack('head')
</head>
<body>
    <div class="container container-paper my-4">
        <div class="print-actions d-flex justify-content-end gap-2">
            @yield('actions')
        </div>

        <div class="card card-soft">
            <div class="card-body">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS sólo para dropdown/modals si usas -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
