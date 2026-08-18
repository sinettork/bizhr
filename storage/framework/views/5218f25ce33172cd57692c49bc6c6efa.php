<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['course' => null]));

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

foreach (array_filter((['course' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<div class="row g-3">
    <div class="col-md-8"><label class="form-label">Course title</label><input class="form-control" name="title" value="<?php echo e(old('title', $course?->title)); ?>" required></div>
    <div class="col-md-4"><label class="form-label">Duration (minutes)</label><input class="form-control" type="number" min="0" max="100000" name="duration_minutes" value="<?php echo e(old('duration_minutes', $course?->duration_minutes ?? 60)); ?>" required></div>
    <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"><?php echo e(old('description', $course?->description)); ?></textarea></div>
    <div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_mandatory" value="1" <?php if(old('is_mandatory', $course?->is_mandatory)): echo 'checked'; endif; ?>><span class="form-check-label">Mandatory training</span></label></div>
    <?php if($course): ?><div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?php if(old('is_active', $course->is_active)): echo 'checked'; endif; ?>><span class="form-check-label">Active course</span></label></div><?php endif; ?>
</div>
<?php /**PATH D:\www\bizhr\resources\views/components/training-course-fields.blade.php ENDPATH**/ ?>