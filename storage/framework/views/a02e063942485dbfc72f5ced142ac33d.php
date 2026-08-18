<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['branches', 'departments', 'announcement' => null]));

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

foreach (array_filter((['branches', 'departments', 'announcement' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<div class="row g-3"><div class="col-md-8"><label class="form-label">Title</label><input class="form-control" name="title" value="<?php echo e(old('title', $announcement?->title)); ?>" required></div><div class="col-md-4"><label class="form-label">Audience</label><select class="form-select" name="audience_type"><?php $__currentLoopData = ['all'=>'All employees','branch'=>'Branch','department'=>'Department']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(old('audience_type', $announcement?->audience_type ?? 'all') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="col-md-6"><label class="form-label">Branch (when audience is Branch)</label><select class="form-select" name="branch_id"><option value="">Not applicable</option><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($branch->id); ?>" <?php if(old('branch_id', $announcement?->branch_id) == $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="col-md-6"><label class="form-label">Department (when audience is Department)</label><select class="form-select" name="department_id"><option value="">Not applicable</option><?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($department->id); ?>" <?php if(old('department_id', $announcement?->department_id) == $department->id): echo 'selected'; endif; ?>><?php echo e($department->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="col-md-6"><label class="form-label">Publish at</label><input class="form-control" type="datetime-local" name="published_at" value="<?php echo e(old('published_at', $announcement?->published_at?->format('Y-m-d\TH:i'))); ?>"></div><div class="col-md-6"><label class="form-label">Expires at</label><input class="form-control" type="datetime-local" name="expires_at" value="<?php echo e(old('expires_at', $announcement?->expires_at?->format('Y-m-d\TH:i'))); ?>"></div><div class="col-12"><label class="form-label">Content <span class="text-danger">*</span></label><textarea class="form-control" name="content" rows="5" required><?php echo e(old('content', $announcement?->content)); ?></textarea></div><div class="col-12 d-flex flex-wrap gap-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_pinned" value="1" <?php if(old('is_pinned', $announcement?->is_pinned)): echo 'checked'; endif; ?>><span class="form-check-label">Pinned</span></label><label class="form-check"><input class="form-check-input" type="checkbox" name="is_urgent" value="1" <?php if(old('is_urgent', $announcement?->is_urgent)): echo 'checked'; endif; ?>><span class="form-check-label">Urgent</span></label><label class="form-check"><input class="form-check-input" type="checkbox" name="requires_acknowledgement" value="1" <?php if(old('requires_acknowledgement', $announcement?->requires_acknowledgement)): echo 'checked'; endif; ?>><span class="form-check-label">Require acknowledgment</span></label></div></div>
<?php /**PATH D:\www\bizhr\resources\views\components\announcement-fields.blade.php ENDPATH**/ ?>