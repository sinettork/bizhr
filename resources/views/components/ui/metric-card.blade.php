@props([
    'value',
    'tone' => 'default',
    'size' => 'xl',
])

@php
    $valueClasses = match ($tone) {
        'success' => 'text-emerald-600 dark:text-emerald-400',
        'warning' => 'text-amber-600 dark:text-amber-400',
        'danger' => 'text-red-600 dark:text-red-400',
        'info' => 'text-blue-600 dark:text-blue-400',
        'muted' => 'text-zinc-500 dark:text-zinc-400',
        default => 'text-zinc-900 dark:text-white',
    };

    $sizeClasses = $size === 'lg' ? 'text-lg' : 'text-2xl';
@endphp

<div
    {{
        $attributes->class(
            'rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900'
        )
    }}
>
    <p class="text-sm text-zinc-500 dark:text-zinc-400">
        {{ $slot }}
    </p>

    <p class="mt-1 font-semibold {{ $sizeClasses }} {{ $valueClasses }}">
        {{ $value }}
    </p>
</div>
