<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'allowSaveNew' => false,
    'saveLabel' => 'Save & close',
    'newLabel' => 'Save & new',
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
    'allowSaveNew' => false,
    'saveLabel' => 'Save & close',
    'newLabel' => 'Save & new',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $repetitiveCreate = in_array($saveLabel, ['Create announcement', 'Assign task'], true);
    $allowSaveNew = $allowSaveNew || $repetitiveCreate;
    $saveLabel = $repetitiveCreate ? 'Save & close' : $saveLabel;
?>

<div class="modal-footer form-save-actions">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
    <?php if($allowSaveNew): ?>
        <button class="btn btn-outline-primary" type="submit" name="save_action" value="new"><i class="fa-solid fa-plus me-1"></i><?php echo e($newLabel); ?></button>
    <?php endif; ?>
    <button class="btn btn-primary" type="submit" name="save_action" value="close"><i class="fa-solid fa-floppy-disk me-1"></i><?php echo e($saveLabel); ?></button>
</div>
<?php /**PATH D:\www\bizhr\resources\views\components\form-save-actions.blade.php ENDPATH**/ ?>