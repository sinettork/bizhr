@props([
    'canEdit' => false,
    'canDelete' => false,
    'editTarget' => null,
    'editUrl' => null,
    'editLabel' => 'Edit',
    'editIcon' => 'fa-pen',
    'deleteUrl' => null,
    'deleteLabel' => 'Delete',
    'deleteIcon' => 'fa-trash',
    'deleteConfirm' => 'This action may remove or archive data. Continue?',
    'deleteConfirmTitle' => null,
    'deleteTone' => 'danger',
    'ariaLabel' => 'Actions',
])

@php
    $hasCustomItems = trim((string) $slot) !== '';
    $hasEdit = $canEdit && ($editTarget || $editUrl);
    $hasDelete = $canDelete && $deleteUrl;
@endphp

@if($hasCustomItems || $hasEdit || $hasDelete)
    <div class="dropdown flex-shrink-0 entity-action-menu">
        <button
            class="btn btn-action-link btn-sm px-1"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            aria-label="{{ $ariaLabel }}"
        >
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm p-1 entity-action-menu-dropdown" style="min-width:150px;font-size:.76rem;">
            @if($hasCustomItems)
                {{ $slot }}
                @if($hasEdit || $hasDelete)
                    <li><hr class="dropdown-divider my-1"></li>
                @endif
            @endif

            @if($hasEdit)
                <li>
                    @if($editTarget)
                        <button class="dropdown-item rounded-1 px-2 py-1" type="button" data-bs-toggle="modal" data-bs-target="{{ $editTarget }}">
                            <i class="fa-solid {{ $editIcon }} me-2"></i>{{ $editLabel }}
                        </button>
                    @elseif($editUrl)
                        <a class="dropdown-item rounded-1 px-2 py-1" href="{{ $editUrl }}">
                            <i class="fa-solid {{ $editIcon }} me-2"></i>{{ $editLabel }}
                        </a>
                    @endif
                </li>
            @endif

            @if($hasDelete)
                @if($hasEdit)
                    <li><hr class="dropdown-divider my-1"></li>
                @endif
                <li>
                    <form
                        method="POST"
                        action="{{ $deleteUrl }}"
                        data-confirm="{{ $deleteConfirm }}"
                        data-confirm-title="{{ $deleteConfirmTitle ?: 'Confirm '.strtolower($deleteLabel) }}"
                        data-confirm-action="{{ $deleteLabel }}"
                        data-confirm-tone="{{ $deleteTone }}"
                    >
                        @csrf
                        @method('DELETE')
                        <button class="dropdown-item rounded-1 px-2 py-1 text-danger" type="submit">
                            <i class="fa-solid {{ $deleteIcon }} me-2"></i>{{ $deleteLabel }}
                        </button>
                    </form>
                </li>
            @endif
        </ul>
    </div>
@endif
