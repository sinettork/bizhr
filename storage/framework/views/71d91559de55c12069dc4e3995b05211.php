<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['asset' => null]));

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

foreach (array_filter((['asset' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<div class="row g-3"><div class="col-md-4"><label class="form-label">Asset code</label><input class="form-control" name="asset_code" value="<?php echo e(old('asset_code', $asset?->asset_code)); ?>" required></div><div class="col-md-4"><label class="form-label">Name</label><input class="form-control" name="name" value="<?php echo e(old('name', $asset?->name)); ?>" required></div><div class="col-md-4"><label class="form-label">Category</label><input class="form-control" name="category" value="<?php echo e(old('category', $asset?->category)); ?>" required></div><div class="col-md-4"><label class="form-label">Serial number</label><input class="form-control" name="serial_number" value="<?php echo e(old('serial_number', $asset?->serial_number)); ?>"></div><div class="col-md-2"><label class="form-label">Purchase date</label><input class="form-control" type="date" name="purchase_date" value="<?php echo e(old('purchase_date', $asset?->purchase_date?->toDateString())); ?>"></div><div class="col-md-2"><label class="form-label">Cost</label><input class="form-control" type="number" step=".01" name="purchase_cost" value="<?php echo e(old('purchase_cost', $asset?->purchase_cost)); ?>"></div><div class="col-md-2"><label class="form-label">Currency</label><select class="form-select" name="currency"><?php $__currentLoopData = ['USD','KHR']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php if(old('currency', $asset?->currency ?? 'USD') === $currency): echo 'selected'; endif; ?>><?php echo e($currency); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="col-md-2"><label class="form-label">Condition</label><select class="form-select" name="condition"><?php $__currentLoopData = ['new','good','fair','poor']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $condition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php if(old('condition', $asset?->condition ?? 'good') === $condition): echo 'selected'; endif; ?>><?php echo e($condition); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes"><?php echo e(old('notes', $asset?->notes)); ?></textarea></div></div>
<?php /**PATH D:\www\bizhr\resources\views\components\asset-fields.blade.php ENDPATH**/ ?>