{{-- resources/views/pdf/partials/items.blade.php --}}
@php
    /** @var \App\Models\Invoice|\App\Models\Budget $doc */
    $doc = $invoice ?? $budget;
@endphp

<table class="items-table w-100" style="table-layout:fixed; width:100%; border-collapse:collapse;">
    <thead>
        <tr>
            <th class="th-desc"  style="width:58%;">DESCRIPCIÓN</th>
            <th class="th-num"   style="width:10%;">CANT.</th>
            <th class="th-num"   style="width:12%;">PRECIO</th>
            <th class="th-num"   style="width:8%;">DTO %</th>
            <th class="th-num"   style="width:8%;">IVA %</th>
            <th class="th-num"   style="width:12%;">TOTAL LÍNEA</th>
        </tr>
    </thead>
    <tbody>
    @foreach($doc->items as $it)
        <tr>
            <td class="td-desc">
                {{ $it->description }}
            </td>
            <td class="td-num">
                {{ number_format($it->quantity, 3, ',', '.') }}
            </td>
            <td class="td-num">
                {{ number_format($it->unit_price, 2, ',', '.') }} {{ $doc->currency ?? 'EUR' }}
            </td>
            <td class="td-num">
                {{ rtrim(rtrim((string)$it->discount, '0'),'.') }}
            </td>
            <td class="td-num">
                {{ rtrim(rtrim((string)$it->tax_rate, '0'),'.') }}
            </td>
            <td class="td-num">
                {{ number_format($it->total_line, 2, ',', '.') }} {{ $doc->currency ?? 'EUR' }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
