<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => $title]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title)]); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => $title,'icon' => $icon,'context' => $workspaceContext]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'context' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($workspaceContext)]); ?>
         <?php $__env->slot('filters', null, []); ?> 
            <form class="reference-filter-form" method="GET">
                <div class="input-group reference-search"><span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span><input class="form-control" name="search" value="<?php echo e($search); ?>" placeholder="Search <?php echo e(strtolower($title)); ?>" aria-label="Search <?php echo e(strtolower($title)); ?>"></div>
                <?php if($hasStatus): ?><select class="form-select reference-status" name="status"><option value="">All statuses</option><?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($option); ?>" <?php if($status === $option): echo 'selected'; endif; ?>><?php echo e(ucfirst(str_replace('_', ' ', $option))); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><?php endif; ?>
                <button class="btn btn-primary reference-search-button" type="submit">Search</button>
            </form>
         <?php $__env->endSlot(); ?>
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
    <?php if (isset($component)) { $__componentOriginal1e74bba73c42cd9ece3463020bbe8198 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e74bba73c42cd9ece3463020bbe8198 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.placeholder-alert','data' => ['isPlaceholder' => $isPlaceholder ?? false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('placeholder-alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['isPlaceholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isPlaceholder ?? false)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e74bba73c42cd9ece3463020bbe8198)): ?>
<?php $attributes = $__attributesOriginal1e74bba73c42cd9ece3463020bbe8198; ?>
<?php unset($__attributesOriginal1e74bba73c42cd9ece3463020bbe8198); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e74bba73c42cd9ece3463020bbe8198)): ?>
<?php $component = $__componentOriginal1e74bba73c42cd9ece3463020bbe8198; ?>
<?php unset($__componentOriginal1e74bba73c42cd9ece3463020bbe8198); ?>
<?php endif; ?>
    <div class="reference-list" data-list-container>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><th class="<?php echo e($loop->first ? 'ps-3' : ''); ?>"><?php echo e(\Illuminate\Support\Str::headline($column)); ?></th><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tr></thead><tbody>
            <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php ($value = data_get($record, $column)); ?><td class="<?php echo e($loop->first ? 'ps-3 fw-medium' : ''); ?>">
                <?php if($column === $statusColumn): ?><span class="badge text-bg-<?php echo e(in_array($value, ['active','approved','paid','completed','verified','present'], true) ? 'success' : (in_array($value, ['rejected','cancelled','failed'], true) ? 'danger' : 'warning')); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $value ?? '—'))); ?></span>
                <?php elseif(is_bool($value)): ?><span class="badge text-bg-<?php echo e($value ? 'success' : 'secondary'); ?>"><?php echo e($value ? 'Yes' : 'No'); ?></span>
                <?php elseif($value instanceof \DateTimeInterface): ?><?php echo e($value->format('d M Y H:i')); ?>

                <?php elseif(in_array($column, ['amount','gross_salary','net_salary','base_salary','minimum_salary','maximum_salary','earned_days','used_days','remaining_days'], true) && $value !== null): ?><?php echo e(number_format((float) $value, 2)); ?>

                <?php else: ?><?php echo e(filled($value) ? $value : '—'); ?><?php endif; ?>
            </td><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td class="py-5 text-center text-body-secondary" colspan="<?php echo e(count($columns)); ?>"><i class="fa-solid <?php echo e($icon); ?> fa-xl d-block mb-3 text-primary"></i>No <?php echo e(strtolower($title)); ?> records found.</td></tr><?php endif; ?>
        </tbody></table></div>
        <?php if (isset($component)) { $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination-footer','data' => ['paginator' => $records]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($records)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1)): ?>
<?php $attributes = $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1; ?>
<?php unset($__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1)): ?>
<?php $component = $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1; ?>
<?php unset($__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1); ?>
<?php endif; ?>
    </div>
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
<?php /**PATH D:\www\bizhr\resources\views/pages/standard.blade.php ENDPATH**/ ?>