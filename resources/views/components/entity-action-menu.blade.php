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
    'deleteConfirm' => 'Are you sure?',
    'ariaLabel' => 'Actions',
])

@if($canEdit || $canDelete)
    <div class="dropdown flex-shrink-0">
        <button
            class="btn btn-action-link btn-sm px-1"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            aria-label="{{ $ariaLabel }}"
        >
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm p-1" style="min-width:150px;font-size:.76rem;">
            @if($canEdit)
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

            @if($canDelete && $deleteUrl)
                @if($canEdit)
                    <li><hr class="dropdown-divider my-1"></li>
                @endif
                <li>
                    <form method="POST" action="{{ $deleteUrl }}" data-confirm="{{ $deleteConfirm }}">
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
