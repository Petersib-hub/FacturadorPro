@extends('layouts.app')
@section('title','Cliente')

@section('content')
<div class="card card-soft">
    <div class="card-body">
        <h5 class="mb-3">{{ $client->name }}</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-2"><strong>Email:</strong> {{ $client->email ?? '—' }}</div>
                <div class="mb-2"><strong>Teléfono:</strong> {{ $client->phone ?? '—' }}</div>
                <div class="mb-2"><strong>NIF/CIF:</strong> {{ $client->tax_id ?? '—' }}</div>
            </div>
            <div class="col-md-6">
                <div class="mb-2"><strong>Dirección:</strong> {{ $client->address ?? '—' }}</div>
                <div class="mb-2"><strong>Ciudad:</strong> {{ $client->zip }} {{ $client->city }}</div>
                <div class="mb-2"><strong>País:</strong> {{ $client->country ?? '—' }}</div>
            </div>
        </div>
        <div class="mt-3">
            <small class="text-muted">
                Consentimiento RGPD: {{ $client->consent_accepted_at ? $client->consent_accepted_at->format('d/m/Y H:i') : 'No registrado' }}
            </small>
        </div>

        {{-- Acciones portal público del cliente --}}
        <hr class="my-4">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            @if($client->public_token)
                @php
                    $portalUrl = route('public.portal.client', $client->public_token);
                @endphp

                <a href="{{ $portalUrl }}" target="_blank" class="btn btn-brand">
                    Abrir portal del cliente
                </a>

                <button type="button" class="btn btn-outline-secondary" id="btnCopyPortal">
                    Copiar enlace del portal
                </button>

                <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const btn = document.getElementById('btnCopyPortal');
                    btn?.addEventListener('click', async () => {
                        try {
                            await navigator.clipboard.writeText(@json($portalUrl));
                            btn.innerText = '¡Copiado!';
                            setTimeout(() => btn.innerText = 'Copiar enlace del portal', 1500);
                        } catch(e) {
                            alert('No se pudo copiar. Enlace: {{ $portalUrl }}');
                        }
                    });
                });
                </script>
            @else
                <div class="alert alert-warning mb-0">
                    Este cliente aún no tiene token público. Guarda el cliente de nuevo para generar uno o crea una pequeña acción para regenerarlo.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
