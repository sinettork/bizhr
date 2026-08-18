@props([
    'icon' => 'fa-inbox',
    'title' => 'Nothing here yet',
    'message' => null,
    'tone' => 'primary',
])

<div {{ $attributes->class(['empty-state d-flex flex-column align-items-center justify-content-center text-center']) }}>
    <span class="empty-state-icon text-{{ $tone }}" aria-hidden="true">
        <i class="fa-solid {{ $icon }}"></i>
    </span>
    <div class="empty-state-title fw-semibold text-dark">{{ $title }}</div>
    @if(filled($message))
        <div class="empty-state-message small text-body-secondary mt-1">{{ $message }}</div>
    @endif
    @if(trim((string) $slot) !== '')
        <div class="empty-state-actions mt-3">{{ $slot }}</div>
    @endif
</div>
