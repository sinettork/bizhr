<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['permissions', 'role' => null]));

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

foreach (array_filter((['permissions', 'role' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<label class="form-label">Role name</label><input class="form-control mb-3" name="name" value="<?php echo e(old('name', $role?->name)); ?>" required><label class="form-label">Permissions</label><div class="row g-3"><?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $modulePermissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="col-md-4"><fieldset class="border rounded p-3 h-100"><legend class="float-none w-auto px-1 fs-6"><?php echo e(str($module)->replace('-', ' ')->title()); ?></legend><?php $__currentLoopData = $modulePermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><label class="form-check"><input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo e($permission->name); ?>" <?php if(in_array($permission->name, old('permissions', $role?->permissions?->pluck('name')->all() ?? []), true)): echo 'checked'; endif; ?>><span class="form-check-label"><?php echo e(str($permission->name)->after('.')->replace('-', ' ')->title()); ?></span></label><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></fieldset></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
<?php /**PATH D:\www\bizhr\resources\views\components\role-fields.blade.php ENDPATH**/ ?>