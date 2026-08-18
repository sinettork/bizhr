<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'addLabel' => 'Add record',
    'addTarget' => null,
    'addModal' => null,
    'importTarget' => null,
    'importLabel' => 'Import CSV',
    'exportUrl' => null,
    'showExport' => false,
    'showPrint' => false,
    'showCompact' => false,
    'showColumns' => false,
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
    'addLabel' => 'Add record',
    'addTarget' => null,
    'addModal' => null,
    'importTarget' => null,
    'importLabel' => 'Import CSV',
    'exportUrl' => null,
    'showExport' => false,
    'showPrint' => false,
    'showCompact' => false,
    'showColumns' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $contextImport = match (request()->route()?->getName()) {
        'branches.index' => ['type' => 'branches', 'permission' => 'branch.create'],
        'departments.index' => ['type' => 'departments', 'permission' => 'department.create'],
        'positions.index' => ['type' => 'positions', 'permission' => 'position.create'],
        'employment-types.index' => ['type' => 'employment-types', 'permission' => 'employment-type.create'],
        'employees.index' => ['type' => 'employees', 'permission' => 'employee.create'],
        default => null,
    };
    $contextImportTarget = $contextImport && auth()->user()?->can($contextImport['permission'])
        ? route('imports.index', ['type' => $contextImport['type']])
        : null;
    $importTarget = $importTarget ?: $contextImportTarget;
?>

<?php if($addTarget || $addModal || $importTarget || $exportUrl || $showExport || $showPrint || $showCompact || $showColumns): ?>
<div class="list-actions d-flex flex-wrap align-items-center justify-content-end gap-1 px-2 py-2" data-list-actions aria-label="List actions">
    <?php if($exportUrl): ?>
        <a class="btn btn-action-link btn-sm" href="<?php echo e($exportUrl); ?>"><i class="fa-solid fa-file-export" aria-hidden="true"></i><span>Export</span></a>
    <?php elseif($showExport): ?>
        <button class="btn btn-action-link btn-sm" type="button" data-list-export><i class="fa-solid fa-file-export" aria-hidden="true"></i><span>Export</span></button>
    <?php endif; ?>
    <?php if($showPrint): ?><button class="btn btn-action-link btn-sm" type="button" data-list-print><i class="fa-solid fa-print" aria-hidden="true"></i><span>Print</span></button><?php endif; ?>
    <?php if($showCompact): ?><button class="btn btn-action-link btn-sm" type="button" data-list-compact aria-pressed="false"><i class="fa-solid fa-list" aria-hidden="true"></i><span>Compact view</span></button><?php endif; ?>
    <?php if($importTarget): ?><a class="btn btn-action-link btn-sm" href="<?php echo e($importTarget); ?>"><i class="fa-solid fa-file-import" aria-hidden="true"></i><span><?php echo e($importLabel); ?></span></a><?php endif; ?>
    <?php if($addModal): ?>
        <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#<?php echo e($addModal); ?>"><i class="fa-solid fa-circle-plus" aria-hidden="true"></i><span><?php echo e($addLabel); ?></span></button>
    <?php elseif($addTarget && \Illuminate\Support\Str::startsWith($addTarget, '#')): ?>
        <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="<?php echo e($addTarget); ?>"><i class="fa-solid fa-circle-plus" aria-hidden="true"></i><span><?php echo e($addLabel); ?></span></button>
    <?php elseif($addTarget): ?>
        <a class="btn btn-action-link btn-sm" href="<?php echo e($addTarget); ?>"><i class="fa-solid fa-circle-plus" aria-hidden="true"></i><span><?php echo e($addLabel); ?></span></a>
    <?php endif; ?>
    <?php if($showColumns): ?><span data-column-chooser></span><?php endif; ?>
</div>
<?php endif; ?>
<?php /**PATH D:\www\bizhr\resources\views\components\list-actions.blade.php ENDPATH**/ ?>