<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Employment contracts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Employment contracts']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Employment contracts','icon' => 'fa-file-signature']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Employment contracts','icon' => 'fa-file-signature']); ?>
         <?php $__env->slot('filters', null, []); ?> <form class="reference-filter-form" method="GET" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true"><div class="input-group reference-search"><span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span><input class="form-control" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search contract or employee" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms"></div><select class="form-select reference-status" name="status" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change"><option value="">All statuses</option><?php $__currentLoopData = ['draft', 'pending_approval', 'active', 'expiring', 'expired', 'terminated', 'superseded']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($status); ?>" <?php if(request('status') === $status): echo 'selected'; endif; ?>><?php echo e(ucwords(str_replace('_', ' ', $status))); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><button class="btn btn-primary reference-search-button" type="submit">Search</button></form> <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('contract.create')): ?><a class="btn btn-action-link btn-sm" href="<?php echo e(route('contracts.create')); ?>"><i class="fa-solid fa-circle-plus"></i><span>Create contract</span></a><?php endif; ?> <?php $__env->endSlot(); ?>
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

    <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>

    <div class="reference-list" data-list-container>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-3">Contract / employee</th><th>Type</th><th>Period</th><th>Salary</th><th>Status</th><th class="text-end pe-3">Actions</th></tr></thead><tbody>
            <?php $__empty_1 = true; $__currentLoopData = $contracts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contract): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="ps-3"><div class="fw-semibold"><?php echo e($contract->contract_number); ?></div><div class="small text-body-secondary"><?php echo e($contract->employee->full_name_km ?: $contract->employee->full_name_en); ?> · <?php echo e($contract->employee->employee_code); ?></div></td>
                    <td><span class="badge text-bg-primary"><?php echo e(strtoupper($contract->type)); ?></span></td>
                    <td class="text-nowrap"><?php echo e($contract->start_date->format('d/m/Y')); ?> – <?php echo e($contract->end_date?->format('d/m/Y') ?? 'Open-ended'); ?></td>
                    <td class="text-nowrap fw-medium"><?php echo e(number_format($contract->salary_amount, $contract->salary_currency === 'KHR' ? 0 : 2)); ?> <?php echo e($contract->salary_currency); ?></td>
                    <td><span class="badge text-bg-<?php echo e(in_array($contract->status, ['active', 'approved'], true) ? 'success' : (in_array($contract->status, ['terminated', 'expired'], true) ? 'secondary' : 'warning')); ?>"><?php echo e(ucwords(str_replace('_', ' ', $contract->status))); ?></span></td>
                    <td class="text-end pe-3 text-nowrap">
                        <?php if($contract->document_path): ?><a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('contracts.download', $contract)); ?>" title="Download PDF"><i class="fa-solid fa-download"></i><span class="visually-hidden">Download PDF</span></a><?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('contract.approve')): ?>
                            <?php if($contract->status === 'pending_approval'): ?><form method="POST" action="<?php echo e(route('contracts.approve', $contract)); ?>" class="d-inline"><?php echo csrf_field(); ?><button class="btn btn-sm btn-success" type="submit">Approve</button></form><?php endif; ?>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('contract.create')): ?>
                            <?php if($contract->type === 'fdc' && in_array($contract->status, ['active', 'expiring'], true)): ?><a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('contracts.renew', $contract)); ?>">Renew</a><?php endif; ?>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('contract.terminate')): ?>
                            <?php if(in_array($contract->status, ['active', 'expiring'], true)): ?><button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#terminate-<?php echo e($contract->id); ?>">Terminate</button><?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('contract.terminate')): ?>
                    <?php if(in_array($contract->status, ['active', 'expiring'], true)): ?><tr class="collapse" id="terminate-<?php echo e($contract->id); ?>"><td colspan="6" class="bg-light"><form method="POST" action="<?php echo e(route('contracts.terminate', $contract)); ?>" class="row g-2 align-items-end"><?php echo csrf_field(); ?><div class="col-md-3"><label class="form-label">Termination date <span class="text-danger">*</span></label><input class="form-control form-control-sm" type="date" name="termination_date" required></div><div class="col-md-6"><label class="form-label">Reason <span class="text-danger">*</span></label><input class="form-control form-control-sm" name="termination_reason" minlength="10" required></div><div class="col-md-3"><button class="btn btn-sm btn-danger" type="submit">Confirm termination</button></div></form></td></tr><?php endif; ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="py-5 text-center text-body-secondary">No employment contracts found.</td></tr>
            <?php endif; ?>
        </tbody></table></div>
        <?php if (isset($component)) { $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination-footer','data' => ['paginator' => $contracts]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contracts)]); ?>
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
<?php endif; ?><?php /**PATH D:\www\bizhr\resources\views\pages\employment-contracts\index.blade.php ENDPATH**/ ?>