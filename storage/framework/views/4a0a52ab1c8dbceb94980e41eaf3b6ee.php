<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Announcements & News']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Announcements & News']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Company News & Announcements','icon' => 'fa-newspaper','context' => 'Communications']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Company News & Announcements','icon' => 'fa-newspaper','context' => 'Communications']); ?>
         <?php $__env->slot('filters', null, []); ?> 
            <span class="small text-body-secondary"><i class="fa-solid fa-bullhorn me-1 text-primary"></i>Official company communications & notices</span>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('announcement.manage')): ?>
                <a class="btn btn-action-link btn-sm" href="<?php echo e(route('announcements.index')); ?>">
                    <i class="fa-solid fa-gear"></i><span>Manage announcements</span>
                </a>
            <?php endif; ?>
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

    <?php if(session('status')): ?>
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span><?php echo e(session('status')); ?></span></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="card mb-3">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <?php if($item->is_urgent): ?>
                                <span class="badge text-bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Urgent</span>
                            <?php endif; ?>
                            <?php if($item->is_pinned): ?>
                                <span class="badge text-bg-primary"><i class="fa-solid fa-thumbtack me-1"></i>Pinned</span>
                            <?php endif; ?>
                            <span class="fw-semibold text-dark"><?php echo e($item->title); ?></span>
                        </div>
                        <div class="small text-body-secondary">
                            <i class="fa-regular fa-calendar me-1"></i><?php echo e($item->published_at?->format('d M Y, h:i A')); ?>

                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 text-dark" style="white-space: pre-line; line-height: 1.6;"><?php echo e($item->content); ?></div>

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top">
                            <div class="small text-body-secondary">
                                <span class="badge text-bg-light border text-dark"><i class="fa-solid fa-users me-1"></i><?php echo e(str($item->audience_type ?? 'All Staff')->title()); ?></span>
                            </div>

                            <?php if($item->requires_acknowledgement): ?>
                                <?php if($item->acknowledged_by_me): ?>
                                    <span class="small text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Acknowledged</span>
                                <?php else: ?>
                                    <form method="POST" action="<?php echo e(route('announcements.acknowledge', $item)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button class="btn btn-sm btn-primary" type="submit">
                                            <i class="fa-solid fa-check-double me-1"></i>Acknowledge reading
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="card">
                    <div class="card-body text-center py-5 text-body-secondary">
                        <i class="fa-solid fa-newspaper fa-xl d-block mb-3 text-primary"></i>
                        No active company announcements at this time.
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-3">
                <?php if (isset($component)) { $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination-footer','data' => ['paginator' => $items]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($items)]); ?>
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
        </div>
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
<?php /**PATH D:\www\bizhr\resources\views/announcements/feed.blade.php ENDPATH**/ ?>