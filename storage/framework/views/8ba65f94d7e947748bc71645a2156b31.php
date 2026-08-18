<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['placeholder' => 'Search...', 'value' => null, 'hxTarget' => '#list-content', 'hxSwap' => 'innerHTML', 'hxTrigger' => 'keyup changed delay:500ms']));

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

foreach (array_filter((['placeholder' => 'Search...', 'value' => null, 'hxTarget' => '#list-content', 'hxSwap' => 'innerHTML', 'hxTrigger' => 'keyup changed delay:500ms']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="input-group reference-search" <?php echo e($attributes); ?>>
    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
    <input 
        class="form-control" 
        name="search" 
        value="<?php echo e($value ?? request('search', '')); ?>" 
        placeholder="<?php echo e($placeholder); ?>"
        hx-get="<?php echo e(request()->url()); ?>"
        hx-target="<?php echo e($hxTarget); ?>"
        hx-swap="<?php echo e($hxSwap); ?>"
        hx-trigger="<?php echo e($hxTrigger); ?>"
        hx-push-url="false"
        <?php echo e($attributes->whereStartsWith('aria')); ?>

    >
</div>
<?php /**PATH D:\www\bizhr\resources\views\components\search-input.blade.php ENDPATH**/ ?>