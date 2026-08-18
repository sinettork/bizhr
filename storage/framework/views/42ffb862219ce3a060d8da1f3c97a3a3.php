<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['paginator']));

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

foreach (array_filter((['paginator']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="reference-list-footer pagination-footer" aria-label="Table pagination">
    <div class="pagination-navigation">
        <?php if($paginator->hasPages()): ?>
            <?php echo e($paginator->onEachSide(1)->links()); ?>

        <?php endif; ?>
    </div>
    <div class="pagination-controls">
        <span class="pagination-summary">Showing <?php echo e(number_format($paginator->firstItem() ?? 0)); ?>-<?php echo e(number_format($paginator->lastItem() ?? 0)); ?> of <?php echo e(number_format($paginator->total())); ?> &middot; Page <?php echo e(number_format($paginator->currentPage())); ?> of <?php echo e(number_format($paginator->lastPage())); ?></span>
        <form class="pagination-size-form" method="GET">
            <?php $__currentLoopData = request()->except(['page', 'per_page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(is_scalar($value)): ?><input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>"><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <label class="visually-hidden" for="per-page-<?php echo e(spl_object_id($paginator)); ?>">Rows per page</label>
            <select id="per-page-<?php echo e(spl_object_id($paginator)); ?>" class="form-select form-select-sm" name="per_page" onchange="this.form.submit()" aria-label="Rows per page">
                <?php $__currentLoopData = [10, 20, 30, 50, 100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($size); ?>" <?php if((int) request('per_page', $paginator->perPage()) === $size): echo 'selected'; endif; ?>><?php echo e($size); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <span>rows</span>
        </form>
    </div>
</div>
<?php /**PATH D:\www\bizhr\resources\views\components\pagination-footer.blade.php ENDPATH**/ ?>