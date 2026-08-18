<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['branches', 'department' => null]));

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

foreach (array_filter((['branches', 'department' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<label class="form-label">Name</label><input class="form-control mb-3" name="name" value="<?php echo e(old('name', $department?->name)); ?>" required>
<label class="form-label">Code</label><input class="form-control mb-3" name="code" value="<?php echo e(old('code', $department?->code)); ?>" placeholder="auto-generate">
<label class="form-label">Branch</label><select class="form-select mb-3" name="branch_id"><option value="">Company-wide</option><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($branch->id); ?>" <?php if(old('branch_id', $department?->branch_id) == $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
<label class="form-label">Manager</label><input class="form-control mb-3" name="manager_name" value="<?php echo e(old('manager_name', $department?->manager_name)); ?>">
<label class="form-label">Phone</label><input class="form-control mb-3" name="phone" value="<?php echo e(old('phone', $department?->phone)); ?>">
<label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" value="<?php echo e(old('email', $department?->email)); ?>">
<label class="form-label">Description</label><textarea class="form-control mb-3" name="description"><?php echo e(old('description', $department?->description)); ?></textarea>
<?php /**PATH D:\www\bizhr\resources\views\components\department-fields.blade.php ENDPATH**/ ?>