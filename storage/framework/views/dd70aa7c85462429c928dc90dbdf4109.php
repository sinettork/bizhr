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
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div><div class="small text-uppercase text-body-secondary fw-semibold">People</div><h1 class="h5 mb-0"><?php echo e($title); ?></h1></div>
        <a class="btn btn-light" href="<?php echo e(route('employees.index')); ?>"><i class="fa-solid fa-arrow-left me-1"></i>Back to employees</a>
    </div>

    <form method="POST" enctype="multipart/form-data" action="<?php echo e($employee->exists ? route('employees.update', $employee) : route('employees.store')); ?>">
        <?php echo csrf_field(); ?>
        <?php if($employee->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card mb-3"><div class="card-header">Work assignment</div><div class="card-body row g-3">
                    <div class="col-md-4"><label class="form-label" for="employee_code">Employee code</label><input class="form-control <?php $__errorArgs = ['employee_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="employee_code" name="employee_code" value="<?php echo e(old('employee_code', $employee->employee_code)); ?>" maxlength="50"><?php $__errorArgs = ['employee_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                    <div class="col-md-4"><label class="form-label" for="branch_id">Branch <span class="text-danger">*</span></label><select class="form-select <?php $__errorArgs = ['branch_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="branch_id" name="branch_id" required><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($branch->id); ?>" <?php if(old('branch_id', $employee->branch_id) == $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><?php $__errorArgs = ['branch_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                    <div class="col-md-4"><label class="form-label" for="department_id">Department <span class="text-danger">*</span></label><select class="form-select <?php $__errorArgs = ['department_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="department_id" name="department_id" required><?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($department->id); ?>" <?php if(old('department_id', $employee->department_id) == $department->id): echo 'selected'; endif; ?> data-branch="<?php echo e($department->branch_id); ?>"><?php echo e($department->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><?php $__errorArgs = ['department_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                    <div class="col-md-4"><label class="form-label" for="position_id">Position</label><select class="form-select" id="position_id" name="position_id"><option value="">No position</option><?php $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($position->id); ?>" <?php if(old('position_id', $employee->position_id) == $position->id): echo 'selected'; endif; ?> data-branch="<?php echo e($position->branch_id); ?>" data-department="<?php echo e($position->department_id); ?>"><?php echo e($position->title); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                    <div class="col-md-4"><label class="form-label" for="employment_type_id">Employment type</label><select class="form-select" id="employment_type_id" name="employment_type_id"><option value="">Not set</option><?php $__currentLoopData = $employmentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($type->id); ?>" <?php if(old('employment_type_id', $employee->employment_type_id) == $type->id): echo 'selected'; endif; ?>><?php echo e($type->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                    <div class="col-md-4"><label class="form-label" for="employment_status">Status</label><select class="form-select" id="employment_status" name="employment_status"><?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($option); ?>" <?php if(old('employment_status', $employee->employment_status) === $option): echo 'selected'; endif; ?>><?php echo e($option); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                </div></div>
                <div class="card mb-3"><div class="card-header">Personal information</div><div class="card-body row g-3">
                    <div class="col-md-6"><label class="form-label" for="first_name">First name</label><input class="form-control <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="first_name" name="first_name" value="<?php echo e(old('first_name', $employee->first_name)); ?>" required><?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                    <div class="col-md-6"><label class="form-label" for="last_name">Last name</label><input class="form-control <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="last_name" name="last_name" value="<?php echo e(old('last_name', $employee->last_name)); ?>" required><?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                    <div class="col-md-6"><label class="form-label" for="full_name_en">Full name (English)</label><input class="form-control" id="full_name_en" name="full_name_en" value="<?php echo e(old('full_name_en', $employee->full_name_en)); ?>"></div>
                    <div class="col-md-6"><label class="form-label" for="full_name_km">Full name (Khmer)</label><input class="form-control" id="full_name_km" name="full_name_km" value="<?php echo e(old('full_name_km', $employee->full_name_km)); ?>"></div>
                    <div class="col-md-4"><label class="form-label" for="gender">Gender</label><select class="form-select" id="gender" name="gender"><option value="">Not set</option><?php $__currentLoopData = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(old('gender', $employee->gender) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                    <div class="col-md-4"><label class="form-label" for="date_of_birth">Date of birth</label><input class="form-control" type="date" id="date_of_birth" name="date_of_birth" value="<?php echo e(old('date_of_birth', $employee->date_of_birth?->format('Y-m-d'))); ?>"></div>
                    <div class="col-12"><label class="form-label">Profile photo</label><div class="row g-3 align-items-center"><div class="col-sm-auto"><label class="image-upload-zone employee-photo-zone" for="profile_photo"><?php if($employee->profile_photo): ?><img id="employee-photo-preview" src="<?php echo e(route('employees.photo',$employee)); ?>" alt="<?php echo e($employee->getFullName()); ?>"><?php else: ?><img id="employee-photo-preview" src="" alt="" hidden><span class="image-upload-placeholder"><i class="fa-solid fa-camera"></i><strong>Choose employee photo</strong><small>Square portrait · max 2 MB</small></span><?php endif; ?></label><input class="visually-hidden" type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/webp" data-image-preview="employee-photo-preview"></div><div class="col"><p class="small text-body-secondary mb-2">Used on the employee profile, directory and printed ID card.</p><?php if($employee->profile_photo): ?><label class="form-check"><input class="form-check-input" type="checkbox" name="remove_profile_photo" value="1"><span class="form-check-label">Remove current photo</span></label><?php endif; ?></div></div></div>
                    <div class="col-md-6"><label class="form-label" for="national_id">National ID</label><input class="form-control" id="national_id" name="national_id" value="<?php echo e(old('national_id', $employee->national_id)); ?>"></div>
                    <div class="col-md-6"><label class="form-label" for="passport_number">Passport number</label><input class="form-control" id="passport_number" name="passport_number" value="<?php echo e(old('passport_number', $employee->passport_number)); ?>"></div>
                </div></div>
                <div class="card"><div class="card-header">Contact and emergency contact</div><div class="card-body row g-3">
                    <div class="col-md-6"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" value="<?php echo e(old('email', $employee->email)); ?>"></div><div class="col-md-6"><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" value="<?php echo e(old('phone', $employee->phone)); ?>"></div>
                    <div class="col-md-8"><label class="form-label" for="address">Address</label><input class="form-control" id="address" name="address" value="<?php echo e(old('address', $employee->address)); ?>"></div><div class="col-md-4"><label class="form-label" for="city">City</label><input class="form-control" id="city" name="city" value="<?php echo e(old('city', $employee->city)); ?>"></div>
                    <div class="col-md-6"><label class="form-label" for="emergency_contact_name">Emergency contact</label><input class="form-control" id="emergency_contact_name" name="emergency_contact_name" value="<?php echo e(old('emergency_contact_name', $employee->emergency_contact_name)); ?>"></div><div class="col-md-6"><label class="form-label" for="emergency_contact_phone">Emergency phone</label><input class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" value="<?php echo e(old('emergency_contact_phone', $employee->emergency_contact_phone)); ?>"></div>
                </div></div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-3"><div class="card-header">Employment dates</div><div class="card-body vstack gap-3">
                    <?php $__currentLoopData = ['hire_date' => 'Hire date', 'probation_end_date' => 'Probation end', 'contract_start_date' => 'Contract start', 'contract_end_date' => 'Contract end', 'id_card_expiry_date' => 'ID card expiry date']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><label class="form-label" for="<?php echo e($field); ?>"><?php echo e($label); ?></label><input class="form-control <?php $__errorArgs = [$field];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="date" id="<?php echo e($field); ?>" name="<?php echo e($field); ?>" value="<?php echo e(old($field, $employee->{$field}?->format('Y-m-d'))); ?>" <?php if($field === 'hire_date'): ?> required <?php endif; ?>><?php $__errorArgs = [$field];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div></div>
                <div class="card mb-3"><div class="card-header">Payroll and payment</div><div class="card-body vstack gap-3">
                    <div><label class="form-label" for="base_salary">Base salary</label><input class="form-control" type="number" min="0" step="0.01" id="base_salary" name="base_salary" value="<?php echo e(old('base_salary', $employee->base_salary)); ?>"></div><div><label class="form-label" for="salary_currency">Currency</label><select class="form-select" id="salary_currency" name="salary_currency"><option value="USD" <?php if(old('salary_currency', $employee->salary_currency) === 'USD'): echo 'selected'; endif; ?>>USD</option><option value="KHR" <?php if(old('salary_currency', $employee->salary_currency) === 'KHR'): echo 'selected'; endif; ?>>KHR</option></select></div>
                    <div><label class="form-label" for="payment_method">Payment method</label><input class="form-control" id="payment_method" name="payment_method" value="<?php echo e(old('payment_method', $employee->payment_method)); ?>"></div><div><label class="form-label" for="bank_name">Bank name</label><input class="form-control" id="bank_name" name="bank_name" value="<?php echo e(old('bank_name', $employee->bank_name)); ?>"></div><div><label class="form-label" for="bank_account_name">Account name</label><input class="form-control" id="bank_account_name" name="bank_account_name" value="<?php echo e(old('bank_account_name', $employee->bank_account_name)); ?>"></div><div><label class="form-label" for="bank_account_number">Account number</label><input class="form-control" id="bank_account_number" name="bank_account_number" value="<?php echo e(old('bank_account_number', $employee->bank_account_number)); ?>"></div>
                </div></div>
                <div class="card"><div class="card-body"><div class="form-check"><input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" <?php if(old('is_active', $employee->is_active)): echo 'checked'; endif; ?>><label class="form-check-label" for="is_active">Employee account is active</label></div></div></div>
                <?php if($employee->exists): ?><div class="card mt-3"><div class="card-header">Change control</div><div class="card-body vstack gap-3"><div><label class="form-label" for="effective_date">Effective date</label><input class="form-control" type="date" id="effective_date" name="effective_date" value="<?php echo e(old('effective_date', today()->format('Y-m-d'))); ?>"></div><div><label class="form-label" for="change_reason">Reason for employment change</label><textarea class="form-control" id="change_reason" name="change_reason" maxlength="1000" placeholder="Required when assignment, salary or status changes"><?php echo e(old('change_reason')); ?></textarea><div class="form-text">Creates a permanent employment-history entry when controlled fields change.</div></div></div></div><?php endif; ?>
            </div>
        </div>
        <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
            <a class="btn btn-light" href="<?php echo e(route('employees.index')); ?>">Close</a>
            <?php if (! ($employee->exists)): ?><button class="btn btn-outline-primary" type="submit" name="save_action" value="new"><i class="fa-solid fa-plus me-1"></i>Save & new</button><?php endif; ?>
            <button class="btn btn-primary" type="submit" name="save_action" value="close"><i class="fa-solid fa-floppy-disk me-1"></i><?php echo e($employee->exists ? 'Save changes' : 'Save & close'); ?></button>
        </div>
    </form>
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

<?php $__env->startPush('scripts'); ?>
<script nonce="<?php echo e(request()->attributes->get('csp_nonce')); ?>">
    document.addEventListener('DOMContentLoaded', () => {
        const branch = document.getElementById('branch_id'), department = document.getElementById('department_id'), position = document.getElementById('position_id');
        const filter = () => {
            [...department.options].forEach(option => { option.hidden = option.dataset.branch && option.dataset.branch !== branch.value; });
            if (department.selectedOptions[0]?.hidden) department.value = '';
            [...position.options].forEach(option => { option.hidden = option.dataset.branch && (option.dataset.branch !== branch.value || option.dataset.department !== department.value); });
            if (position.selectedOptions[0]?.hidden) position.value = '';
        };
        branch.addEventListener('change', filter); department.addEventListener('change', filter); filter();
    });
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH D:\www\bizhr\resources\views\employees\form.blade.php ENDPATH**/ ?>