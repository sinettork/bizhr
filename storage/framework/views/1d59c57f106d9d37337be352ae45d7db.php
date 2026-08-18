<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Employee documents']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Employee documents']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Employee documents','icon' => 'fa-folder-open','context' => $employee->full_name_km ?: $employee->full_name_en ?: $employee->employee_code]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Employee documents','icon' => 'fa-folder-open','context' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($employee->full_name_km ?: $employee->full_name_en ?: $employee->employee_code)]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a class="btn btn-action-link btn-sm" href="<?php echo e(route('employees.show', $employee)); ?>">
                <i class="fa-solid fa-arrow-left"></i><span>Employee profile</span>
            </a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['employee.edit', 'employee.edit-own'])): ?>
                <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#uploadDocument">
                    <i class="fa-solid fa-circle-plus"></i><span>Add document</span>
                </button>
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
        <div class="alert alert-success"><?php echo e(session('status')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Document</th>
                        <th>Number</th>
                        <th>Version</th>
                        <th>Issued</th>
                        <th>Expires</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium"><?php echo e($document->document_type); ?></div>
                                <small class="text-body-secondary"><?php echo e($document->original_name); ?></small>
                            </td>
                            <td><?php echo e($document->document_number ?: '—'); ?></td>
                            <td>v<?php echo e($document->version); ?></td>
                            <td><?php echo e($document->issued_date?->format('d M Y') ?? '—'); ?></td>
                            <td>
                                <?php echo e($document->expiry_date?->format('d M Y') ?? '—'); ?>

                                <?php if($document->expiry_date?->isPast() && $document->status !== 'revoked'): ?>
                                    <span class="badge text-bg-danger ms-1">Expired</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge text-bg-<?php echo e($document->status === 'verified' ? 'success' : ($document->status === 'revoked' ? 'secondary' : 'warning')); ?>">
                                    <?php echo e(str($document->status)->replace('_', ' ')->title()); ?>

                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <a class="btn btn-action-link btn-sm" href="<?php echo e(route('employees.documents.download', [$employee, $document])); ?>">
                                    <i class="fa-solid fa-download"></i><span>Download</span>
                                </a>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('employee.view-sensitive')): ?>
                                    <?php if($document->status === 'pending_verification'): ?>
                                        <form class="d-inline" method="POST" action="<?php echo e(route('employees.documents.verify', [$employee, $document])); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button class="btn btn-action-link btn-sm" type="submit">
                                                <i class="fa-solid fa-circle-check"></i><span>Verify</span>
                                            </button>
                                        </form>
                                    <?php elseif($document->status === 'verified'): ?>
                                        <button class="btn btn-action-link btn-sm text-danger" type="button" data-bs-toggle="modal" data-bs-target="#revokeDocument<?php echo e($document->id); ?>">
                                            <i class="fa-solid fa-ban"></i><span>Revoke</span>
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['employee.edit', 'employee.edit-own'])): ?>
                                    <?php if($document->status === 'pending_verification'): ?>
                                        <form class="d-inline" method="POST" action="<?php echo e(route('employees.documents.destroy', [$employee, $document])); ?>" data-confirm="Remove this unverified document?">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn-action-link btn-sm text-danger" type="submit">
                                                <i class="fa-solid fa-trash"></i><span>Remove</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="text-center text-body-secondary py-5">No documents have been uploaded.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (isset($component)) { $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination-footer','data' => ['paginator' => $documents]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($documents)]); ?>
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

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['employee.edit', 'employee.edit-own'])): ?>
        <div class="modal fade" id="uploadDocument" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" enctype="multipart/form-data" action="<?php echo e(route('employees.documents.store', $employee)); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h2 class="modal-title fs-5">Add employee document</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Document type <span class="text-danger">*</span></label><input class="form-control" name="document_type" required></div>
                            <div class="col-md-6"><label class="form-label">Document number</label><input class="form-control" name="document_number"></div>
                            <div class="col-12"><label class="form-label">File <span class="text-danger">*</span></label><input class="form-control" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required><div class="form-text">PDF, image, or Word document; maximum 10 MB.</div></div>
                            <div class="col-md-6"><label class="form-label">Issued date</label><input class="form-control" type="date" name="issued_date"></div>
                            <div class="col-md-6"><label class="form-label">Expiry date</label><input class="form-control" type="date" name="expiry_date"></div>
                            <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
                        </div>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['saveLabel' => 'Upload & close']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['save-label' => 'Upload & close']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc)): ?>
<?php $attributes = $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc; ?>
<?php unset($__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc)): ?>
<?php $component = $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc; ?>
<?php unset($__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc); ?>
<?php endif; ?>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('employee.view-sensitive')): ?>
        <?php $__currentLoopData = $documents->where('status', 'verified'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="modal fade" id="revokeDocument<?php echo e($document->id); ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="<?php echo e(route('employees.documents.revoke', [$employee, $document])); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header"><h2 class="modal-title fs-5">Revoke document</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <p class="mb-3">The file will be retained for audit history.</p>
                            <label class="form-label">Revocation reason <span class="text-danger">*</span></label><textarea class="form-control" name="reason" minlength="5" required></textarea>
                        </div>
                        <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-danger" type="submit">Revoke document</button></div>
                    </form>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php endif; ?>
<?php /**PATH D:\www\bizhr\resources\views/employees/documents.blade.php ENDPATH**/ ?>