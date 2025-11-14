{{-- resources/views/budgets/_actions.blade.php --}}
@php
    /** @var \App\Models\Budget $budget */
    $publicUrl = $budget->public_token ? route('public.budgets.show', $budget->public_token) : null;
@endphp
<ul class="dropdown-menu dropdown-menu-end">
    <li><a class="dropdown-item" href="{{ route('budgets.edit', $budget) }}">Editar</a></li>
    <li><a class="dropdown-item" href="{{ route('budgets.pdf', $budget) }}" target="_blank" rel="noopener">Descargar PDF</a></li>
    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#sendBudgetModal-{{ $budget->id }}">Enviar por email</a></li>

    <li><hr class="dropdown-divider"></li>
    <li class="dropdown-header">Cambiar estado</li>
    <li>
        <form method="post" action="{{ route('budgets.status', $budget) }}">
            @csrf
            <input type="hidden" name="status" value="draft">
            <button class="dropdown-item" type="submit">Borrador</button>
        </form>
    </li>
    <li>
        <form method="post" action="{{ route('budgets.status', $budget) }}">
            @csrf
            <input type="hidden" name="status" value="sent">
            <button class="dropdown-item" type="submit">Enviado</button>
        </form>
    </li>
    <li>
        <form method="post" action="{{ route('budgets.status', $budget) }}">
            @csrf
            <input type="hidden" name="status" value="accepted">
            <button class="dropdown-item" type="submit">Aceptado</button>
        </form>
    </li>
    <li>
        <form method="post" action="{{ route('budgets.status', $budget) }}">
            @csrf
            <input type="hidden" name="status" value="rejected">
            <button class="dropdown-item" type="submit">Rechazado</button>
        </form>
    </li>
    <li>
        <form method="post" action="{{ route('budgets.status', $budget) }}">
            @csrf
            <input type="hidden" name="status" value="pending">
            <button class="dropdown-item" type="submit">Pendiente</button>
        </form>
    </li>

    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item" href="{{ route('budgets.convert', $budget) }}"
           onclick="event.preventDefault(); document.getElementById('convert-{{ $budget->id }}').submit();">
           Convertir a factura
        </a>
        <form id="convert-{{ $budget->id }}" class="d-none" method="post" action="{{ route('budgets.convert', $budget) }}">
            @csrf
        </form>
    </li>

    @if($publicUrl)
        <li><a class="dropdown-item" href="{{ $publicUrl }}" target="_blank" rel="noopener">Abrir enlace público</a></li>
    @endif
</ul>

{{-- Modal de enviar presupuesto --}}
<div class="modal fade" id="sendBudgetModal-{{ $budget->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="post" action="{{ route('budgets.email', $budget) }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Enviar {{ $budget->number }} por email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Para</label>
                    <input type="email" name="to" class="form-control" required value="{{ $budget->client->email ?? '' }}">
                </div>
                <div class="mb-2">
                    <label class="form-label">Asunto</label>
                    <input type="text" name="subject" class="form-control" maxlength="190" placeholder="Presupuesto {{ $budget->number }}">
                </div>
                <div class="mb-2">
                    <label class="form-label">CC</label>
                    <input type="email" name="cc" class="form-control">
                </div>
                <div class="mb-2">
                    <label class="form-label">BCC</label>
                    <input type="email" name="bcc" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-brand">Enviar</button>
            </div>
        </form>
    </div>
</div>
