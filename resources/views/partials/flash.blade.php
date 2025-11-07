{{-- resources/views/partials/flash.blade.php --}}
@if(session('ok') || session('warning') || session('error'))
    <div class="container my-2">
        @if(session('ok'))
            <div class="alert alert-success">{{ session('ok') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
    </div>
@endif
