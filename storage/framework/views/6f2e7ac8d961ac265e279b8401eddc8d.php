<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Review attendance corrections']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Review attendance corrections']); ?><?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Review attendance corrections','icon' => 'fa-list-check','context' => 'Time and attendance']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Review attendance corrections','icon' => 'fa-list-check','context' => 'Time and attendance']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890)): ?>
<?php $attributes = $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890; ?>
<?php unset($__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890)): ?>
<?php $component = $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890; ?>
<?php unset($__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890); ?>
<?php endif; ?><?php if(session('status')): ?><div class="alert alert-success"><?php echo e(session('status')); ?></div><?php endif; ?>
<div class="reference-list" data-list-container><div class="reference-list-toolbar"><h2 class="h6 mb-0">Pending requests</h2></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-3">Employee</th><th>Date / original</th><th>Requested change</th><th>Reason</th><th class="text-end pe-3">Decision</th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $corrections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $correction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td class="ps-3 fw-semibold"><?php echo e($correction->employee?->getFullName()); ?><small class="d-block text-body-secondary"><?php echo e($correction->employee?->department?->name); ?></small></td><td><?php echo e($correction->attendance?->work_date?->format('d M Y')); ?><small class="d-block text-body-secondary"><?php echo e($correction->attendance?->check_in_at?->format('H:i') ?? '—'); ?> / <?php echo e($correction->attendance?->check_out_at?->format('H:i') ?? '—'); ?></small></td><td><?php echo e($correction->requested_check_in?->format('H:i') ?? '—'); ?> / <?php echo e($correction->requested_check_out?->format('H:i') ?? '—'); ?></td><td><?php echo e($correction->reason); ?></td><td class="text-end pe-3"><form class="d-inline" method="POST" action="<?php echo e(route('attendance.corrections.approve',$correction)); ?>"><?php echo csrf_field(); ?><button class="btn btn-sm btn-success" type="submit">Approve</button></form><button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#reject<?php echo e($correction->id); ?>">Reject</button><div class="collapse mt-2" id="reject<?php echo e($correction->id); ?>"><form method="POST" action="<?php echo e(route('attendance.corrections.reject',$correction)); ?>" class="d-flex gap-1"><?php echo csrf_field(); ?><input class="form-control form-control-sm" name="note" required minlength="3" placeholder="Reason"><button class="btn btn-sm btn-danger" type="submit">Confirm</button></form></div></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" class="py-5 text-center text-body-secondary">No pending corrections.</td></tr><?php endif; ?></tbody></table></div><?php if($corrections->hasPages()): ?><div class="reference-list-footer"><?php echo e($corrections->links()); ?></div><?php endif; ?></div> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal81a506f898233b9e7d58286e6bea3c18)): ?>
<?php $attributes = $__attributesOriginal81a506f898233b9e7d58286e6bea3c18; ?>
<?php unset($__attributesOriginal81a506f898233b9e7d58286e6bea3c18); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal81a506f898233b9e7d58286e6bea3c18)): ?>
<?php $component = $__componentOriginal81a506f898233b9e7d58286e6bea3c18; ?>
<?php unset($__componentOriginal81a506f898233b9e7d58286e6bea3c18); ?>
<?php endif; ?>
<?php /**PATH D:\www\bizhr\resources\views\attendance\corrections\review.blade.php ENDPATH**/ ?>