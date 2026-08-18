<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['branches', 'departments', 'position' => null]));

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

foreach (array_filter((['branches', 'departments', 'position' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Title</label><input class="form-control" name="title" value="<?php echo e(old('title', $position?->title)); ?>" required></div>
    <div class="col-md-6"><label class="form-label">Code</label><input class="form-control" name="code" value="<?php echo e(old('code', $position?->code)); ?>" placeholder="auto-generate"></div>
    <div class="col-md-6"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="">All branches</option><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($branch->id); ?>" <?php if(old('branch_id', $position?->branch_id) == $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
    <div class="col-md-6"><label class="form-label">Department</label><select class="form-select" name="department_id"><option value="">All departments</option><?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($department->id); ?>" <?php if(old('department_id', $position?->department_id) == $department->id): echo 'selected'; endif; ?>><?php echo e($department->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
    <div class="col-md-4"><label class="form-label">Minimum salary</label><input class="form-control" type="number" step="0.01" name="minimum_salary" value="<?php echo e(old('minimum_salary', $position?->minimum_salary)); ?>"></div>
    <div class="col-md-4"><label class="form-label">Maximum salary</label><input class="form-control" type="number" step="0.01" name="maximum_salary" value="<?php echo e(old('maximum_salary', $position?->maximum_salary)); ?>"></div>
    <div class="col-md-4"><label class="form-label">Sort order</label><input class="form-control" type="number" name="sort_order" value="<?php echo e(old('sort_order', $position?->sort_order ?? 0)); ?>"></div>
    <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description"><?php echo e(old('description', $position?->description)); ?></textarea></div>
</div>
<?php /**PATH D:\www\bizhr\resources\views/components/position-fields.blade.php ENDPATH**/ ?>