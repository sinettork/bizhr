@php
    $actionFeedback = session('action_feedback');
    $flashToasts = collect(is_array($actionFeedback) && array_is_list($actionFeedback) ? $actionFeedback : [$actionFeedback])
        ->filter(fn ($message) => filled($message))
        ->values();
@endphp

@if($flashToasts->isNotEmpty())
    <div class="app-toast-region" aria-live="polite" aria-atomic="true">
        @foreach($flashToasts as $message)
            <div class="toast app-action-toast" role="status" aria-live="polite" aria-atomic="true" data-app-toast data-bs-autohide="true" data-bs-delay="3800">
                <div class="app-action-toast-indicator" aria-hidden="true"></div>
                <div class="app-action-toast-icon" aria-hidden="true">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="toast-body">{{ $message }}</div>
                <button type="button" class="btn-close app-action-toast-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        @endforeach
    </div>
@endif
