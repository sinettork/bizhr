@props([
    'title',
    'icon' => 'fa-table-list',
    'context' => null,
])

<section class="workspace-command-bar" aria-label="{{ $title }} workspace">
    <div class="workspace-command-title">
        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
        <h1>{{ $title }}</h1>
    </div>
    @isset($filters)
        <div class="workspace-command-filters">{{ $filters }}</div>
    @endisset
    @isset($actions)
        <div class="workspace-command-actions">{{ $actions }}</div>
    @endisset
</section>
