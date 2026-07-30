@props([
    'sidebar' => false,
])

@php
    $brandName = config('app.name', 'BizHR');
@endphp

@if ($sidebar)
    <flux:sidebar.brand :name="$brandName" {{ $attributes }}>
        <x-slot
            name="logo"
            class="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-xl"
        >
            <x-app-logo-icon class="size-9" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$brandName" {{ $attributes }}>
        <x-slot
            name="logo"
            class="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-xl"
        >
            <x-app-logo-icon class="size-9" />
        </x-slot>
    </flux:brand>
@endif
