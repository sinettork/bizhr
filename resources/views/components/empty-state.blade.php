@props([
    'icon' => 'fa-inbox',
    'title' => 'Nothing here yet',
    'message' => null,
])

<div {{ $attributes->class(['empty-state d-flex flex-column align-items-center justify-content-center text-center']) }}>
    <span class="d-inline-flex align-items-center justify-content-center text-primary mb-2" aria-hidden="true" style="width:32px;height:32px;">
        <i class="fa-regular {{ $icon }}" style="font-size:1.25rem;"></i>
    </span>
    <div class="fw-semibold text-dark">{{ $title }}</div>
    @if(filled($message))
        <div class="small text-body-secondary mt-1">{{ $message }}</div>
    @endif
    @if(trim((string) $slot) !== '')
        <div class="mt-3">{{ $slot }}</div>
    @endif
</div>
