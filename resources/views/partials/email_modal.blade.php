@props([
'action' => '#',
'to' => null,
'id' => 'modalEmail',
'title' => 'Enviar por email',
'help' => 'Se adjuntará el PDF.',
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="post" action="{{ $action }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Para</label>
                    <input type="email" name="to" class="form-control" value="{{ $to }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Asunto (opcional)</label>
                    <input type="text" name="subject" class="form-control" maxlength="190">
                </div>
                <div class="small text-muted">{{ $help }}</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-brand">Enviar</button>
            </div>
        </form>
    </div>
</div>
