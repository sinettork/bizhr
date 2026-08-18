@props([
    'addLabel' => 'Add record',
    'addTarget' => null,
    'addModal' => null,
    'importTarget' => null,
    'importLabel' => 'Import CSV',
    'exportUrl' => null,
    'showExport' => false,
    'showPrint' => false,
    'showCompact' => false,
    'showColumns' => false,
])

@php
    $contextImport = match (request()->route()?->getName()) {
        'branches.index' => ['type' => 'branches', 'permission' => 'branch.create'],
        'departments.index' => ['type' => 'departments', 'permission' => 'department.create'],
        'positions.index' => ['type' => 'positions', 'permission' => 'position.create'],
        'employment-types.index' => ['type' => 'employment-types', 'permission' => 'employment-type.create'],
        'employees.index' => ['type' => 'employees', 'permission' => 'employee.create'],
        default => null,
    };
    $contextImportTarget = $contextImport && auth()->user()?->can($contextImport['permission'])
        ? route('imports.index', ['type' => $contextImport['type']])
        : null;
    $importTarget = $importTarget ?: $contextImportTarget;
@endphp

@if ($addTarget || $addModal || $importTarget || $exportUrl || $showExport || $showPrint || $showCompact || $showColumns)
<div class="list-actions d-flex flex-wrap align-items-center justify-content-end gap-1 px-2 py-2" data-list-actions aria-label="List actions">
    @if ($exportUrl)
        <a class="btn btn-action-link btn-sm" href="{{ $exportUrl }}"><i class="fa-solid fa-file-export" aria-hidden="true"></i><span>Export</span></a>
    @elseif ($showExport)
        <button class="btn btn-action-link btn-sm" type="button" data-list-export><i class="fa-solid fa-file-export" aria-hidden="true"></i><span>Export</span></button>
    @endif
    @if ($showPrint)<button class="btn btn-action-link btn-sm" type="button" data-list-print><i class="fa-solid fa-print" aria-hidden="true"></i><span>Print</span></button>@endif
    @if ($showCompact)<button class="btn btn-action-link btn-sm" type="button" data-list-compact aria-pressed="false"><i class="fa-solid fa-list" aria-hidden="true"></i><span>Compact view</span></button>@endif
    @if ($importTarget)<a class="btn btn-action-link btn-sm" href="{{ $importTarget }}"><i class="fa-solid fa-file-import" aria-hidden="true"></i><span>{{ $importLabel }}</span></a>@endif
    @if ($addModal)
        <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#{{ $addModal }}"><i class="fa-solid fa-circle-plus" aria-hidden="true"></i><span>{{ $addLabel }}</span></button>
    @elseif ($addTarget && \Illuminate\Support\Str::startsWith($addTarget, '#'))
        <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="{{ $addTarget }}"><i class="fa-solid fa-circle-plus" aria-hidden="true"></i><span>{{ $addLabel }}</span></button>
    @elseif ($addTarget)
        <a class="btn btn-action-link btn-sm" href="{{ $addTarget }}"><i class="fa-solid fa-circle-plus" aria-hidden="true"></i><span>{{ $addLabel }}</span></a>
    @endif
    @if ($showColumns)<span data-column-chooser></span>@endif
</div>
@endif
