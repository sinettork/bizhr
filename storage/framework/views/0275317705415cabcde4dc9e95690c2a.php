<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Payroll settings']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Payroll settings']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Payroll settings','icon' => 'fa-sliders','context' => 'Payroll configuration']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Payroll settings','icon' => 'fa-sliders','context' => 'Payroll configuration']); ?>
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
    <div class="card shadow-sm border-0"><form method="POST" action="<?php echo e(route('payroll.settings.update')); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?><div class="card-body row g-3"><div class="col-md-3"><label class="form-label">KHR per USD</label><input class="form-control" type="number" step=".01" name="khr_per_usd" value="<?php echo e($settings->khr_per_usd); ?>" required></div><div class="col-md-3"><label class="form-label">Working days/month</label><input class="form-control" type="number" name="working_days_per_month" value="<?php echo e($settings->working_days_per_month); ?>" required></div><div class="col-md-3"><label class="form-label">Hours/day</label><input class="form-control" type="number" step=".25" name="hours_per_day" value="<?php echo e($settings->hours_per_day); ?>" required></div><div class="col-md-3"><label class="form-label">Overtime multiplier</label><input class="form-control" type="number" step=".01" name="default_overtime_multiplier" value="<?php echo e($settings->default_overtime_multiplier); ?>" required></div><div class="col-md-3"><label class="form-label">Dependent relief (KHR)</label><input class="form-control" type="number" name="dependent_relief_khr" value="<?php echo e($settings->dependent_relief_khr); ?>" required></div><div class="col-md-3"><label class="form-label">NSSF employee health %</label><input class="form-control" type="number" step=".01" name="nssf_employee_health_rate" value="<?php echo e($settings->nssf_employee_health_rate); ?>" required></div><div class="col-md-3"><label class="form-label">NSSF employer health %</label><input class="form-control" type="number" step=".01" name="nssf_employer_health_rate" value="<?php echo e($settings->nssf_employer_health_rate); ?>" required></div><div class="col-md-3"><label class="form-label">NSSF employer risk %</label><input class="form-control" type="number" step=".01" name="nssf_employer_risk_rate" value="<?php echo e($settings->nssf_employer_risk_rate); ?>" required></div><div class="col-12 d-flex flex-wrap gap-4"><input type="hidden" name="require_overtime_approval" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="require_overtime_approval" value="1" <?php if($settings->require_overtime_approval): echo 'checked'; endif; ?>><span class="form-check-label">Require overtime approval</span></label><input type="hidden" name="deduct_unpaid_absence" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="deduct_unpaid_absence" value="1" <?php if($settings->deduct_unpaid_absence): echo 'checked'; endif; ?>><span class="form-check-label">Deduct unpaid absence</span></label><input type="hidden" name="salary_tax_enabled" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="salary_tax_enabled" value="1" <?php if($settings->salary_tax_enabled): echo 'checked'; endif; ?>><span class="form-check-label">Calculate salary tax</span></label></div></div><div class="card-footer bg-white text-end">
    <?php if(auth()->user()->can('payroll.approve')): ?>
        <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save settings</button>
    <?php endif; ?>
    </div></form></div>
    <div class="reference-list mt-3"><div class="reference-list-toolbar"><strong>Public holidays</strong><span class="small text-body-secondary">Use this list to verify upcoming payroll calculations.</span></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-3">Date</th><th>Holiday</th><th>Pay treatment</th></tr></thead><tbody>
    <?php $__empty_1 = true; $__currentLoopData = $holidays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $holiday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr><td class="ps-3"><?php echo e($holiday->holiday_date->format('d/m/Y')); ?></td><td><?php echo e($holiday->name); ?></td><td><?php echo e($holiday->is_paid ? 'Paid holiday' : 'Unpaid holiday'); ?></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="3" class="py-4 text-center text-body-secondary">No public holidays configured.</td></tr>
    <?php endif; ?>
    </tbody></table></div></div>
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
<?php /**PATH D:\www\bizhr\resources\views/payroll/settings/index.blade.php ENDPATH**/ ?>