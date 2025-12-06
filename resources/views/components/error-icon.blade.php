{{-- resources/views/components/error-icon.blade.php --}}
@props(['icon' => 'exclamation-triangle'])

<div class="error-icon">
    <i class="fas fa-{{ $icon }}"></i>
</div>