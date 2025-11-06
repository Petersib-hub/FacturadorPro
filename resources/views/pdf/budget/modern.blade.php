{{-- resources/views/pdf/budget/modern.blade.php --}}
@php($doc = $budget)
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $budget->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0f172a; }
        header { margin-bottom: 14px; }
        .w-100 { width:100%; }
        .text-right { text-align:right; }
        .text-left { text-align:left; }

        /* ====== Tabla de ítems segura ====== */
        .items-table { table-layout: fixed; width:100%; border-collapse: collapse; }
        .items-table th, .items-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #cbd5e1;
            vertical-align: top;
        }
        .items-table thead th {
            background: #f1f5f9;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        /* Columna descripción: respeta saltos y corta palabras interminables */
        .items-table .th-desc, .items-table .td-desc {
            white-space: pre-wrap;      /* respeta \n */
            word-break: break-word;     /* corta palabras largas */
            overflow-wrap: anywhere;    /* fuerza corte si es necesario */
        }
        /* Columnas numéricas */
        .items-table .th-num, .items-table .td-num {
            text-align: right;
            white-space: nowrap;
        }

        .totals-table { margin-top: 12px; }
        .totals-table .grand-total .value { font-size: 15px; font-weight: 800; }

        .notes { margin-top: 12px; }
        .badge { display:inline-block; padding:3px 10px; border-radius:9999px; border:1px solid #0f172a; font-size:11px; }
        .muted { color:#64748b; }
        .small { font-size:11px; }
        .logo { max-height:56px; }

        .grid { display: table; width: 100%; }
        .col { display: table-cell; vertical-align: top; }
        .col-7 { width: 70%; }
        .col-5 { width: 30%; }
    </style>
</head>
<body>
    @include('pdf.partials.header', ['budget' => $budget])
    @include('pdf.partials.client_box', ['invoice' => $invoice ?? null, 'budget' => $budget ?? null])

    {{-- Ítems con estilos anti-desbordes --}}
    @include('pdf.partials.items', ['budget' => $budget])

    @include('pdf.partials.totals', ['budget' => $budget])
    @include('pdf.partials.notes', ['budget' => $budget])
    @include('pdf.partials.signatures', ['invoice' => $invoice ?? null, 'budget' => $budget ?? null])
</body>
</html>
