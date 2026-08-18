<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Import data']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Import data']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Import data','icon' => 'fa-file-import']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Import data','icon' => 'fa-file-import']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890)): ?>
<?php $attributes = $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890; ?>
<?php unset($__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890)): ?>
<?php $component = $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890; ?>
<?php unset($__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890); ?>
<?php endif; ?>
    <?php if(session('status')): ?><div class="alert alert-success"><?php echo e(session('status')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>
    <div class="alert alert-info small mb-3"><strong>Recommended order:</strong> Branches → Departments → Positions and Employment types → Employees. Excel (.xlsx) is recommended; CSV remains supported. Download the template, keep the column order unchanged, preview errors, then confirm. Existing records with the same code are updated; other rows are created.</div>
    <div class="row g-3">
        <?php $__empty_1 = true; $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-6 col-xl-4" <?php if($selectedType === $key): ?> id="selected-import" <?php endif; ?>>
                <div class="card border-0 shadow-sm h-100 <?php echo e($selectedType === $key ? 'border border-primary' : ''); ?>">
                    <div class="card-body">
                        <h2 class="h6"><?php echo e($type['label']); ?></h2>
                        <p class="small text-body-secondary">Excel (.xlsx) recommended · CSV supported · up to 2,000 rows</p>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <a class="btn btn-action-link btn-sm" href="<?php echo e(route('imports.template', $key)); ?>"><i class="fa-solid fa-file-excel"></i><span>Excel template</span></a>
                            <a class="btn btn-action-link btn-sm" href="<?php echo e(route('imports.template', ['type' => $key, 'format' => 'csv'])); ?>"><i class="fa-solid fa-file-csv"></i><span>CSV</span></a>
                        </div>
                        <form method="POST" action="<?php echo e(route('imports.preview', $key)); ?>" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3"><label class="form-label">Excel or CSV file <span class="text-danger">*</span></label><input class="form-control" type="file" name="file" accept=".xlsx,.csv,text/csv" required></div>
                            <button class="btn btn-primary w-100"><i class="fa-solid fa-eye"></i> Preview import</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12"><div class="alert alert-secondary">Your account does not have permission to import data.</div></div>
        <?php endif; ?>
    </div>
    <?php if($selectedType): ?><script nonce="<?php echo e(request()->attributes->get('csp_nonce')); ?>">document.getElementById('selected-import')?.scrollIntoView({behavior:'smooth',block:'center'});</script><?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal81a506f898233b9e7d58286e6bea3c18)): ?>
<?php $attributes = $__attributesOriginal81a506f898233b9e7d58286e6bea3c18; ?>
<?php unset($__attributesOriginal81a506f898233b9e7d58286e6bea3c18); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal81a506f898233b9e7d58286e6bea3c18)): ?>
<?php $component = $__componentOriginal81a506f898233b9e7d58286e6bea3c18; ?>
<?php unset($__componentOriginal81a506f898233b9e7d58286e6bea3c18); ?>
<?php endif; ?>
<?php /**PATH D:\www\bizhr\resources\views\imports\index.blade.php ENDPATH**/ ?>