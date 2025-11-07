{{-- resources/views/verifactu/status-badge.blade.php --}}
@php
    use Illuminate\Support\Str;
    // Fuente de estado: explícito ($status) o desde $invoice
    $status = $status ?? ($invoice->verifactu_status ?? null);
@endphp

@if($status)
    @php
        $class = match($status) {
            'verified' => 'bg-success',
            'pending'  => 'bg-warning text-dark',
            'failed'   => 'bg-danger',
            'annulled' => 'bg-secondary',
            default    => 'bg-secondary',
        };
    @endphp
    <span class="badge {{ $class }}">Veri*factu: {{ ucfirst($status) }}</span>

    {{-- Mostrar hash si viene en el modelo --}}
    @if(!empty($invoice) && !empty($invoice->verification_hash))
        <small class="text-muted ms-2">Hash: {{ Str::limit($invoice->verification_hash, 18) }}</small>
    @endif
@endif
