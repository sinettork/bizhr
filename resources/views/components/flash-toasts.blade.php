@php
    $flashToasts = collect([
        ['type' => 'success', 'message' => session('success')],
        ['type' => 'success', 'message' => session('status')],
    ])->filter(fn (array $toast) => filled($toast['message']))->unique('message')->values();
@endphp

@if($flashToasts->isNotEmpty())
    <div class="app-toast-region" aria-live="polite" aria-atomic="true">
        @foreach($flashToasts as $toast)
            <div class="toast app-action-toast" role="status" aria-live="polite" aria-atomic="true" data-app-toast data-bs-autohide="true" data-bs-delay="3800">
                <div class="app-action-toast-indicator" aria-hidden="true"></div>
                <div class="app-action-toast-icon" aria-hidden="true">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="toast-body">{{ $toast['message'] }}</div>
                <button type="button" class="btn-close app-action-toast-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        @endforeach
    </div>
@endif
