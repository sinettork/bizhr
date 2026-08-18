<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'value',
    'tone' => 'default',
    'size' => 'xl',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'value',
    'tone' => 'default',
    'size' => 'xl',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $valueClasses = match ($tone) {
        'success' => 'text-emerald-600 dark:text-emerald-400',
        'warning' => 'text-amber-600 dark:text-amber-400',
        'danger' => 'text-red-600 dark:text-red-400',
        'info' => 'text-blue-600 dark:text-blue-400',
        'muted' => 'text-zinc-500 dark:text-zinc-400',
        default => 'text-zinc-900 dark:text-white',
    };

    $sizeClasses = $size === 'lg' ? 'text-lg' : 'text-2xl';
?>

<div
    <?php echo e($attributes->class(
            'rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900'
        )); ?>

>
    <p class="text-sm text-zinc-500 dark:text-zinc-400">
        <?php echo e($slot); ?>

    </p>

    <p class="mt-1 font-semibold <?php echo e($sizeClasses); ?> <?php echo e($valueClasses); ?>">
        <?php echo e($value); ?>

    </p>
</div>
<?php /**PATH D:\www\bizhr\resources\views\components\ui\metric-card.blade.php ENDPATH**/ ?>