@props([
    'approveUrl',
    'rejectTarget',
    'approveLabel' => 'Approve',
    'rejectLabel' => 'Reject',
    'approveIcon' => 'fa-check',
    'rejectIcon' => 'fa-xmark',
    'compact' => true,
    'approveFields' => [],
    'approveConfirm' => 'Approve this request? Please confirm before continuing.',
    'approveConfirmTitle' => 'Confirm approval',
])

<div {{ $attributes->class(['d-flex flex-wrap gap-2 decision-actions']) }}>
    <button class="btn btn-outline-danger {{ $compact ? 'btn-sm' : '' }}" type="button" data-bs-toggle="modal" data-bs-target="{{ $rejectTarget }}">
        <i class="fa-solid {{ $rejectIcon }} me-1"></i>{{ $rejectLabel }}
    </button>

    <form
        method="POST"
        action="{{ $approveUrl }}"
        data-confirm="{{ $approveConfirm }}"
        data-confirm-title="{{ $approveConfirmTitle }}"
        data-confirm-action="{{ $approveLabel }}"
        data-confirm-tone="success"
    >
        @csrf
        @foreach($approveFields as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <button class="btn btn-success {{ $compact ? 'btn-sm' : '' }}" type="submit">
            <i class="fa-solid {{ $approveIcon }} me-1"></i>{{ $approveLabel }}
        </button>
    </form>
</div>
