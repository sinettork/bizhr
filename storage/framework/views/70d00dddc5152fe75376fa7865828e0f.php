<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['roles', 'employees', 'user' => null]));

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

foreach (array_filter((['roles', 'employees', 'user' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<div class="row g-3"><div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="<?php echo e(old('name', $user?->name)); ?>" required></div><div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?php echo e(old('email', $user?->email)); ?>" required></div><div class="col-12"><label class="form-label">Linked employee</label><select class="form-select" name="employee_id"><option value="">Administrative account (not linked)</option><?php if($user?->employee): ?><option value="<?php echo e($user->employee->id); ?>" selected><?php echo e($user->employee->employee_code); ?> · <?php echo e($user->employee->full_name_en ?: $user->employee->first_name.' '.$user->employee->last_name); ?></option><?php endif; ?> <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($employee->id); ?>" <?php if(old('employee_id') == $employee->id): echo 'selected'; endif; ?>><?php echo e($employee->employee_code); ?> · <?php echo e($employee->full_name_en ?: $employee->first_name.' '.$employee->last_name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="col-12"><label class="form-label d-block">Roles</label><div class="row g-2"><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="col-md-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="roles[]" value="<?php echo e($role->name); ?>" <?php if(in_array($role->name, old('roles', $user?->getRoleNames()->all() ?? []), true)): echo 'checked'; endif; ?>><span class="form-check-label"><?php echo e($role->name); ?></span></label></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div></div></div>
<?php /**PATH D:\www\bizhr\resources\views\components\user-access-fields.blade.php ENDPATH**/ ?>