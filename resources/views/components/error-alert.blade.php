@props(['type' => 'error', 'message' => '', 'dismissible' => true])

@php
    $types = [
        'error' => ['bg' => 'bg-danger', 'icon' => 'exclamation-circle'],
        'warning' => ['bg' => 'bg-warning', 'icon' => 'exclamation-triangle'],
        'success' => ['bg' => 'bg-success', 'icon' => 'check-circle'],
        'info' => ['bg' => 'bg-info', 'icon' => 'info-circle'],
    ];
    
    $config = $types[$type] ?? $types['error'];
@endphp

<div class="alert {{ $config['bg'] }} alert-dismissible fade show" role="alert">
    <i class="fas fa-{{ $config['icon'] }} mr-2"></i>
    {{ $message ?? $slot }}
    
    @if($dismissible)
        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
        </button>
    @endif
</div>