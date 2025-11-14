@extends('layouts.app')
@section('title', 'Presupuesto')

@section('content')
    {{-- Flashes ya se muestran en el layout --}}

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0">Presupuesto {{ $budget->number }}</h4>

        {{-- Botón + dropdown (responsive) --}}
        <div class="btn-group">
            <a href="{{ route('budgets.pdf', $budget) }}" class="btn btn-sm btn-success" target="_blank" rel="noopener">
                PDF
            </a>
            <button type="button" class="btn btn-sm btn-success dropdown-toggle dropdown-toggle-split"
                data-bs-toggle="dropdown" aria-expanded="false">
                <span class="visually-hidden">Más</span>
            </button>
            
            @include('budgets._actions', ['budget' => $budget])

        </div>
    </div>

    @php
        $map = [
            'draft' => ['Borrador', 'bg-secondary'],
            'sent' => ['Enviado', 'bg-info text-dark'],
            'accepted' => ['Aceptado', 'bg-success'],
            'rejected' => ['Rechazado', 'bg-danger'],
            'expired' => ['Vencido', 'bg-warning text-dark'],
        ];
        $sLabel = $map[$budget->status][0] ?? ucfirst($budget->status);
        $sClass = $map[$budget->status][1] ?? 'bg-light text-dark';
    @endphp

    <div class="card card-soft mb-3">
        <div class="card-body">
            <div class="row gy-2">
                <div class="col-md-6">
                    <div><strong>Cliente:</strong> {{ $budget->client->name }}</div>
                    @if($budget->client->email)
                        <div><strong>Email:</strong> {{ $budget->client->email }}</div>
                    @endif
                    @if($budget->client->tax_id)
                        <div><strong>NIF/CIF:</strong> {{ $budget->client->tax_id }}</div>
                    @endif
                </div>
                <div class="col-md-6">
                    <div><strong>Fecha:</strong> {{ optional($budget->date)->format('d/m/Y') }}</div>
                    <div><strong>Válido hasta:</strong> {{ optional($budget->due_date)->format('d/m/Y') ?? '—' }}</div>
                    <div><strong>Estado:</strong> <span class="badge {{ $sClass }}">{{ $sLabel }}</span></div>
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
                                <td style="white-space:pre-wrap;word-break:break-word;overflow-wrap:anywhere;">
                                    {{ $it->description }}
                                </td>
                                <td class="text-end">{{ number_format($it->quantity, 3, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($it->unit_price, 2, ',', '.') }} €</td>
                                <td class="text-end">{{ rtrim(rtrim((string) $it->discount, '0'), '.') }}</td>
                                <td class="text-end">{{ rtrim(rtrim((string) $it->tax_rate, '0'), '.') }}</td>
                                <td class="text-end">{{ number_format($it->total_line, 2, ',', '.') }} €</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 text-end">
                <div><strong>Subtotal:</strong> {{ number_format($budget->subtotal, 2, ',', '.') }} €</div>
                <div><strong>Impuestos:</strong> {{ number_format($budget->tax_total, 2, ',', '.') }} €</div>
                <div class="fs-5"><strong>Total:</strong> {{ number_format($budget->total, 2, ',', '.') }} €</div>
            </div>
        </div>
    </div>

    {{-- Si usas términos / notas en show --}}
    @if($budget->notes || $budget->terms)
        <div class="card card-soft">
            <div class="card-body">
                @if($budget->notes)
                    <div class="mb-2"><strong>Notas</strong></div>
                    <div class="text-muted" style="white-space:pre-wrap">{{ $budget->notes }}</div>
                @endif
                @if($budget->terms)
                    <hr>
                    <div class="mb-2"><strong>Términos</strong></div>
                    <div class="text-muted" style="white-space:pre-wrap">{{ $budget->terms }}</div>
                @endif
            </div>
        </div>
    @endif

    {{-- Modal Enviar --}}
    <div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="post" action="{{ route('budgets.email', $budget) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="sendEmailModalLabel">Enviar presupuesto por email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Para</label>
                        <input type="email" name="to" class="form-control" required
                            value="{{ old('to', $budget->client->email ?? '') }}" placeholder="cliente@dominio.com">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Asunto (opcional)</label>
                        <input type="text" name="subject" class="form-control" maxlength="190"
                            placeholder="Presupuesto {{ $budget->number }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">CC (opcional)</label>
                        <input type="email" name="cc" class="form-control" placeholder="copias@dominio.com">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">BCC (opcional)</label>
                        <input type="email" name="bcc" class="form-control" placeholder="ocultas@dominio.com">
                    </div>
                    <div class="small text-muted">Se adjuntará el PDF del presupuesto.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-brand">Enviar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function copyBudgetPublicLink(url) {
            navigator.clipboard.writeText(url).then(() => {
                const n = document.createElement('div');
                n.className = 'alert alert-success mt-2';
                n.innerText = 'Enlace copiado al portapapeles.';
                document.querySelector('.container-xxl, .container').prepend(n);
                setTimeout(() => n.remove(), 2200);
            });
        }
    </script>
@endpush
