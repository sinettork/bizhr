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

<div {{ $attributes->class(['d-flex flex-wrap align-items-center gap-2 decision-actions']) }}>
    <button class="btn btn-action-link text-danger {{ $compact ? 'btn-sm' : '' }}" type="button" data-bs-toggle="modal" data-bs-target="{{ $rejectTarget }}">
        <i class="fa-solid {{ $rejectIcon }}"></i><span>{{ $rejectLabel }}</span>
    </button>

    <form
        method="POST"
        action="{{ $approveUrl }}"
        data-confirm="{{ $approveConfirm }}"
        data-confirm-title="{{ $approveConfirmTitle }}"
        data-confirm-action="{{ $approveLabel }}"
        data-confirm-tone="primary"
    >
        @csrf
        @foreach($approveFields as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <button class="btn btn-primary {{ $compact ? 'btn-sm' : '' }}" type="submit">
            <i class="fa-solid {{ $approveIcon }} me-1"></i>{{ $approveLabel }}
        </button>
    </form>
</div>
