<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th class="ps-3 pe-2 text-center" style="width: 42px;">
                    <input class="form-check-input employee-select-all" type="checkbox" aria-label="Select all employees">
                </th>
                <th class="ps-0">Code</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Contact</th>
                <th>Status</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="ps-3 pe-2 text-center">
                        <input class="form-check-input employee-row-select" type="checkbox" value="<?php echo e($employee->id); ?>" aria-label="Select <?php echo e($employee->full_name_en ?: trim($employee->first_name.' '.$employee->last_name)); ?>">
                    </td>
                    <td class="ps-0 fw-medium"><?php echo e($employee->employee_code); ?></td>
                    <td>
                        <a class="fw-semibold text-body" href="<?php echo e(route('employees.show', $employee)); ?>"><?php echo e($employee->full_name_en ?: trim($employee->first_name.' '.$employee->last_name)); ?></a>
                        <?php if($employee->position): ?><div class="small text-body-secondary"><?php echo e($employee->position->title); ?></div><?php endif; ?>
                    </td>
                    <td><?php echo e($employee->department?->name ?? '—'); ?><div class="small text-body-secondary"><?php echo e($employee->branch?->name); ?></div></td>
                    <td><?php echo e($employee->email ?: '—'); ?><div class="small text-body-secondary"><?php echo e($employee->phone); ?></div></td>
                    <td><span class="badge text-bg-<?php echo e($employee->is_active ? 'success' : 'secondary'); ?>"><?php echo e($employee->employment_status); ?></span></td>
                    <td class="text-end pe-3 text-nowrap">
                        <a class="btn btn-sm btn-link text-primary" href="<?php echo e(route('employees.show', $employee)); ?>" title="View employee"><i class="fa-solid fa-eye"></i><span class="visually-hidden">View</span></a>
                        <?php if(auth()->user()->can('employee.edit') || (auth()->user()->can('employee.edit-own') && $employee->user_id === auth()->id())): ?>
                            <a class="btn btn-sm btn-link text-primary" href="<?php echo e(route('employees.edit', $employee)); ?>" title="Edit employee"><i class="fa-solid fa-pen"></i><span class="visually-hidden">Edit</span></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="py-5 text-center text-body-secondary"><i class="fa-solid fa-users fa-xl d-block mb-3 text-primary"></i>No employees found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if (isset($component)) { $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination-footer','data' => ['paginator' => $employees]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($employees)]); ?>
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
<?php /**PATH D:\www\bizhr\resources\views\employees\_table.blade.php ENDPATH**/ ?>