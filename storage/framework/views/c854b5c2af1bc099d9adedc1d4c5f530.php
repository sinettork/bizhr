<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'icon' => 'fa-table-list',
    'context' => null,
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
    'title',
    'icon' => 'fa-table-list',
    'context' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section class="workspace-command-bar" aria-label="<?php echo e($title); ?> workspace">
    <div class="workspace-command-title">
        <i class="fa-solid <?php echo e($icon); ?>" aria-hidden="true"></i>
        <h1><?php echo e($title); ?></h1>
    </div>
    <?php if(isset($filters)): ?>
        <div class="workspace-command-filters"><?php echo e($filters); ?></div>
    <?php endif; ?>
    <?php if(isset($actions)): ?>
        <div class="workspace-command-actions"><?php echo e($actions); ?></div>
    <?php endif; ?>
</section>
<?php /**PATH D:\www\bizhr\resources\views\components\workspace-command-bar.blade.php ENDPATH**/ ?>