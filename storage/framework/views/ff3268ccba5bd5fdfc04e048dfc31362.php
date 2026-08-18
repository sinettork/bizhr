<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Manage announcements']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Manage announcements']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Manage announcements','icon' => 'fa-bullhorn']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Manage announcements','icon' => 'fa-bullhorn']); ?>
         <?php $__env->slot('filters', null, []); ?> <form method="GET" class="reference-filter-form"><div class="input-group reference-search"><span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span><input class="form-control" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search announcement" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms"></div><button class="btn btn-primary reference-search-button">Search</button></form> <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> <?php if (isset($component)) { $__componentOriginal32c85afa77cc4fec54802bf6365d2208 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal32c85afa77cc4fec54802bf6365d2208 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.list-actions','data' => ['addModal' => auth()->user()->can('announcement.manage') ? 'createAnnouncement' : null,'addLabel' => 'Add announcement']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('list-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['add-modal' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user()->can('announcement.manage') ? 'createAnnouncement' : null),'add-label' => 'Add announcement']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal32c85afa77cc4fec54802bf6365d2208)): ?>
<?php $attributes = $__attributesOriginal32c85afa77cc4fec54802bf6365d2208; ?>
<?php unset($__attributesOriginal32c85afa77cc4fec54802bf6365d2208); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal32c85afa77cc4fec54802bf6365d2208)): ?>
<?php $component = $__componentOriginal32c85afa77cc4fec54802bf6365d2208; ?>
<?php unset($__componentOriginal32c85afa77cc4fec54802bf6365d2208); ?>
<?php endif; ?> <?php $__env->endSlot(); ?>
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
    <div class="reference-list" data-list-container><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-3">Announcement</th><th>Audience</th><th>Publish / expiry</th><th>Status</th><th>Acknowledged</th><th class="text-end pe-3">Actions</th></tr></thead><tbody>
        <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td class="ps-3"><div class="fw-semibold"><?php echo e($item->title); ?> <?php if($item->is_pinned): ?><i class="fa-solid fa-thumbtack text-primary"></i><?php endif; ?> <?php if($item->is_urgent): ?><span class="badge text-bg-danger">Urgent</span><?php endif; ?></div><small class="text-body-secondary"><?php echo e(str($item->content)->limit(100)); ?></small></td><td><?php echo e(str($item->audience_type)->title()); ?></td><td><?php echo e($item->published_at?->format('d M Y H:i') ?: 'Draft'); ?><small class="d-block text-body-secondary">Expires: <?php echo e($item->expires_at?->format('d M Y') ?: 'Never'); ?></small></td><td><span class="badge text-bg-<?php echo e($item->published_at && $item->published_at->isPast() ? 'success' : 'secondary'); ?>"><?php echo e(!$item->published_at ? 'Draft' : ($item->published_at->isFuture() ? 'Scheduled' : 'Published')); ?></span></td><td><?php echo e($item->requires_acknowledgement ? $item->acknowledgements_count : 'Not required'); ?></td><td class="text-end pe-3"><?php if(!$item->published_at): ?><form class="d-inline" method="POST" action="<?php echo e(route('announcements.publish', $item)); ?>"><?php echo csrf_field(); ?><button class="btn btn-action-link btn-sm" type="submit"><i class="fa-solid fa-paper-plane"></i><span>Publish</span></button></form><?php endif; ?><button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#edit<?php echo e($item->id); ?>"><i class="fa-solid fa-pen"></i><span>Edit</span></button><form class="d-inline" method="POST" action="<?php echo e(route('announcements.destroy', $item)); ?>" data-confirm="Archive this announcement?"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-action-link btn-sm text-danger" type="submit"><i class="fa-solid fa-box-archive"></i><span>Archive</span></button></form></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="py-5 text-center text-body-secondary">No announcements found.</td></tr><?php endif; ?>
    </tbody></table></div><?php if (isset($component)) { $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination-footer','data' => ['paginator' => $announcements]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($announcements)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1)): ?>
<?php $attributes = $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1; ?>
<?php unset($__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1)): ?>
<?php $component = $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1; ?>
<?php unset($__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1); ?>
<?php endif; ?></div>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('announcement.manage')): ?>
        <div class="modal fade" id="createAnnouncement" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="<?php echo e(route('announcements.store')); ?>"><?php echo csrf_field(); ?><div class="modal-header"><h2 class="modal-title fs-5">Create announcement</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><?php if (isset($component)) { $__componentOriginal38824b9b37eafc05706181e468bf518a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38824b9b37eafc05706181e468bf518a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.announcement-fields','data' => ['branches' => $branches,'departments' => $departments]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('announcement-fields'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['branches' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($branches),'departments' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($departments)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal38824b9b37eafc05706181e468bf518a)): ?>
<?php $attributes = $__attributesOriginal38824b9b37eafc05706181e468bf518a; ?>
<?php unset($__attributesOriginal38824b9b37eafc05706181e468bf518a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal38824b9b37eafc05706181e468bf518a)): ?>
<?php $component = $__componentOriginal38824b9b37eafc05706181e468bf518a; ?>
<?php unset($__componentOriginal38824b9b37eafc05706181e468bf518a); ?>
<?php endif; ?><label class="form-check mt-3"><input class="form-check-input" type="checkbox" name="publish_now" value="1" checked><span class="form-check-label">Publish now (uncheck to use scheduled publish time)</span></label></div><?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['allowSaveNew' => true,'saveLabel' => 'Save & close','newLabel' => 'Save & new']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['allow-save-new' => true,'save-label' => 'Save & close','new-label' => 'Save & new']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc)): ?>
<?php $attributes = $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc; ?>
<?php unset($__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc)): ?>
<?php $component = $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc; ?>
<?php unset($__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc); ?>
<?php endif; ?></form></div></div>
        <?php $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="modal fade" id="edit<?php echo e($item->id); ?>" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="<?php echo e(route('announcements.update', $item)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?><div class="modal-header"><h2 class="modal-title fs-5">Edit announcement</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><?php if (isset($component)) { $__componentOriginal38824b9b37eafc05706181e468bf518a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38824b9b37eafc05706181e468bf518a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.announcement-fields','data' => ['branches' => $branches,'departments' => $departments,'announcement' => $item]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('announcement-fields'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['branches' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($branches),'departments' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($departments),'announcement' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal38824b9b37eafc05706181e468bf518a)): ?>
<?php $attributes = $__attributesOriginal38824b9b37eafc05706181e468bf518a; ?>
<?php unset($__attributesOriginal38824b9b37eafc05706181e468bf518a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal38824b9b37eafc05706181e468bf518a)): ?>
<?php $component = $__componentOriginal38824b9b37eafc05706181e468bf518a; ?>
<?php unset($__componentOriginal38824b9b37eafc05706181e468bf518a); ?>
<?php endif; ?></div><?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['saveLabel' => 'Save & close']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['save-label' => 'Save & close']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc)): ?>
<?php $attributes = $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc; ?>
<?php unset($__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc)): ?>
<?php $component = $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc; ?>
<?php unset($__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc); ?>
<?php endif; ?></form></div></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal81a506f898233b9e7d58286e6bea3c18)): ?>
<?php $attributes = $__attributesOriginal81a506f898233b9e7d58286e6bea3c18; ?>
<?php unset($__attributesOriginal81a506f898233b9e7d58286e6bea3c18); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal81a506f898233b9e7d58286e6bea3c18)): ?>
<?php $component = $__componentOriginal81a506f898233b9e7d58286e6bea3c18; ?>
<?php unset($__componentOriginal81a506f898233b9e7d58286e6bea3c18); ?>
<?php endif; ?><?php /**PATH D:\www\bizhr\resources\views\announcements\index.blade.php ENDPATH**/ ?>