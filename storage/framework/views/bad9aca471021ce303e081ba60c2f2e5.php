<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => $employee->full_name_en ?: $employee->employee_code]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($employee->full_name_en ?: $employee->employee_code)]); ?>
    <!-- Top Command & Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <div class="small text-uppercase text-body-secondary fw-semibold">
                <a href="<?php echo e(route('employees.index')); ?>" class="text-decoration-none text-body-secondary">Employees</a>
                <span class="mx-1">/</span>
                <span>Employee Profile</span>
            </div>
            <h1 class="h5 mb-0 fw-bold text-dark">
                <?php echo e($employee->full_name_en ?: trim($employee->first_name.' '.$employee->last_name)); ?>

                <?php if($employee->full_name_km): ?>
                    <span class="text-body-secondary fs-6 fw-normal ms-1">(<?php echo e($employee->full_name_km); ?>)</span>
                <?php endif; ?>
            </h1>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a class="btn btn-light btn-sm" href="<?php echo e(route('employees.index')); ?>">
                <i class="fa-solid fa-arrow-left me-1"></i>Employees
            </a>
            <a class="btn btn-outline-primary btn-sm" href="<?php echo e(route('employees.id-card', $employee)); ?>">
                <i class="fa-solid fa-id-card me-1"></i>ID card
            </a>
            <?php if(auth()->user()->can('employee.edit') || (auth()->user()->can('employee.edit-own') && $employee->user_id === auth()->id())): ?>
                <a class="btn btn-primary btn-sm" href="<?php echo e(route('employees.edit', $employee)); ?>">
                    <i class="fa-solid fa-pen me-1"></i>Edit profile
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if(session('success') || session('status')): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-circle-check"></i><span><?php echo e(session('success') ?: session('status')); ?></span>
        </div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-circle-exclamation"></i><span><?php echo e($errors->first()); ?></span>
        </div>
    <?php endif; ?>

    <!-- Profile 360 Navigation Tabs -->
    <ul class="nav profile-nav-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="profile-nav-tab active" id="tab-personal-btn" data-bs-toggle="tab" data-bs-target="#tab-personal" type="button" role="tab" aria-controls="tab-personal" aria-selected="true">
                <i class="fa-solid fa-user-circle"></i><span>Personal info</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="profile-nav-tab" id="tab-employment-btn" data-bs-toggle="tab" data-bs-target="#tab-employment" type="button" role="tab" aria-controls="tab-employment" aria-selected="false">
                <i class="fa-solid fa-briefcase"></i><span>Employee details</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="profile-nav-tab" id="tab-payroll-btn" data-bs-toggle="tab" data-bs-target="#tab-payroll" type="button" role="tab" aria-controls="tab-payroll" aria-selected="false">
                <i class="fa-solid fa-money-check-dollar"></i><span>Payroll details</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="profile-nav-tab" id="tab-documents-btn" data-bs-toggle="tab" data-bs-target="#tab-documents" type="button" role="tab" aria-controls="tab-documents" aria-selected="false">
                <i class="fa-solid fa-file-invoice"></i><span>Documents</span>
                <span class="badge text-bg-light border ms-1"><?php echo e($employee->documents->count()); ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="profile-nav-tab" id="tab-history-btn" data-bs-toggle="tab" data-bs-target="#tab-history" type="button" role="tab" aria-controls="tab-history" aria-selected="false">
                <i class="fa-solid fa-clock-rotate-left"></i><span>History</span>
                <span class="badge text-bg-light border ms-1"><?php echo e($employee->employmentHistories->count()); ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Tab 1: Personal Info -->
        <div class="tab-pane fade show active" id="tab-personal" role="tabpanel" aria-labelledby="tab-personal-btn">
            <!-- Basic Information Hero Banner -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">
                        <i class="fa-solid fa-id-badge text-primary"></i><span>Basic information</span>
                    </h2>
                    <?php if(auth()->user()->can('employee.edit') || (auth()->user()->can('employee.edit-own') && $employee->user_id === auth()->id())): ?>
                        <a href="<?php echo e(route('employees.edit', $employee)); ?>" class="btn btn-action-link btn-sm" title="Edit basic information">
                            <i class="fa-solid fa-pen"></i><span>Edit</span>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="profile-card-body">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-6">
                            <div class="profile-hero">
                                <?php if($employee->profile_photo): ?>
                                    <img class="profile-hero-avatar" src="<?php echo e(route('employees.photo', $employee)); ?>" alt="<?php echo e($employee->full_name_en); ?>">
                                <?php else: ?>
                                    <div class="profile-hero-avatar-placeholder">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="profile-hero-meta">
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <span class="profile-hero-name"><?php echo e($employee->full_name_en ?: trim($employee->first_name.' '.$employee->last_name)); ?></span>
                                        <span class="badge text-bg-<?php echo e($employee->is_active ? 'success' : 'secondary'); ?>">
                                            <?php echo e(str($employee->employment_status ?: ($employee->is_active ? 'Active' : 'Inactive'))->title()); ?>

                                        </span>
                                    </div>
                                    <div class="profile-hero-sub fw-semibold">
                                        <code><?php echo e($employee->employee_code); ?></code>
                                        <?php if($employee->position): ?>
                                            <span class="mx-1">·</span><span><?php echo e($employee->position->title); ?></span>
                                        <?php endif; ?>
                                        <?php if($employee->department): ?>
                                            <span class="mx-1">·</span><span><?php echo e($employee->department->name); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="profile-hero-contact">
                                        <?php if($employee->gender): ?>
                                            <span><i class="fa-solid fa-venus-mars me-1 text-body-secondary"></i><?php echo e(ucfirst($employee->gender)); ?></span>
                                        <?php endif; ?>
                                        <?php if($employee->email): ?>
                                            <a href="mailto:<?php echo e($employee->email); ?>"><i class="fa-regular fa-envelope me-1 text-body-secondary"></i><?php echo e($employee->email); ?></a>
                                        <?php endif; ?>
                                        <?php if($employee->phone): ?>
                                            <a href="tel:<?php echo e($employee->phone); ?>"><i class="fa-solid fa-phone me-1 text-body-secondary"></i><?php echo e($employee->phone); ?></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 border-start-lg">
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <div class="profile-kv-row">
                                        <span class="profile-kv-label">Date of birth</span>
                                        <span class="profile-kv-value"><?php echo e($employee->date_of_birth?->format('d M Y') ?? '—'); ?></span>
                                    </div>
                                    <div class="profile-kv-row">
                                        <span class="profile-kv-label">Age</span>
                                        <span class="profile-kv-value"><?php echo e($employee->date_of_birth ? $employee->date_of_birth->age.' years' : '—'); ?></span>
                                    </div>
                                    <div class="profile-kv-row">
                                        <span class="profile-kv-label">Gender</span>
                                        <span class="profile-kv-value"><?php echo e($employee->gender ? ucfirst($employee->gender) : '—'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="profile-kv-row">
                                        <span class="profile-kv-label">National ID</span>
                                        <span class="profile-kv-value font-monospace"><?php echo e($employee->national_id ?: '—'); ?></span>
                                    </div>
                                    <div class="profile-kv-row">
                                        <span class="profile-kv-label">Passport</span>
                                        <span class="profile-kv-value font-monospace"><?php echo e($employee->passport_number ?: '—'); ?></span>
                                    </div>
                                    <div class="profile-kv-row">
                                        <span class="profile-kv-label">City</span>
                                        <span class="profile-kv-value"><?php echo e($employee->city ?: '—'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2-Column Cards Grid: Address & Emergency Contact -->
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="profile-card h-100 mb-0">
                        <div class="profile-card-header">
                            <h2 class="profile-card-title">
                                <i class="fa-solid fa-location-dot text-primary"></i><span>Address & identification</span>
                            </h2>
                            <?php if(auth()->user()->can('employee.edit')): ?>
                                <a href="<?php echo e(route('employees.edit', $employee)); ?>" class="btn btn-action-link btn-sm">
                                    <i class="fa-solid fa-pen"></i><span>Edit</span>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="profile-card-body">
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Residential address</span>
                                <span class="profile-kv-value text-break"><?php echo e($employee->address ?: 'Not specified'); ?></span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">City / Province</span>
                                <span class="profile-kv-value"><?php echo e($employee->city ?: '—'); ?></span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">National ID</span>
                                <span class="profile-kv-value font-monospace"><?php echo e($employee->national_id ?: '—'); ?></span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Passport number</span>
                                <span class="profile-kv-value font-monospace"><?php echo e($employee->passport_number ?: '—'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="profile-card h-100 mb-0">
                        <div class="profile-card-header">
                            <h2 class="profile-card-title">
                                <i class="fa-solid fa-phone-volume text-primary"></i><span>Emergency contact</span>
                            </h2>
                            <?php if(auth()->user()->can('employee.edit')): ?>
                                <a href="<?php echo e(route('employees.edit', $employee)); ?>" class="btn btn-action-link btn-sm">
                                    <i class="fa-solid fa-pen"></i><span>Edit</span>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="profile-card-body">
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Contact person name</span>
                                <span class="profile-kv-value"><?php echo e($employee->emergency_contact_name ?: 'Not recorded'); ?></span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Phone number</span>
                                <span class="profile-kv-value">
                                    <?php if($employee->emergency_contact_phone): ?>
                                        <a href="tel:<?php echo e($employee->emergency_contact_phone); ?>" class="text-decoration-none fw-semibold">
                                            <?php echo e($employee->emergency_contact_phone); ?>

                                        </a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Alternative phone</span>
                                <span class="profile-kv-value"><?php echo e($employee->phone ?: '—'); ?></span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Primary email</span>
                                <span class="profile-kv-value"><?php echo e($employee->email ?: '—'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Employment Details -->
        <div class="tab-pane fade" id="tab-employment" role="tabpanel" aria-labelledby="tab-employment-btn">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="profile-card h-100 mb-0">
                        <div class="profile-card-header">
                            <h2 class="profile-card-title">
                                <i class="fa-solid fa-sitemap text-primary"></i><span>Organization & role</span>
                            </h2>
                            <?php if(auth()->user()->can('employee.edit')): ?>
                                <a href="<?php echo e(route('employees.edit', $employee)); ?>" class="btn btn-action-link btn-sm">
                                    <i class="fa-solid fa-pen"></i><span>Edit</span>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="profile-card-body">
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Branch</span>
                                <span class="profile-kv-value"><?php echo e($employee->branch?->name ?? '—'); ?></span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Department</span>
                                <span class="profile-kv-value"><?php echo e($employee->department?->name ?? '—'); ?></span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Job title / Position</span>
                                <span class="profile-kv-value"><?php echo e($employee->position?->title ?? '—'); ?></span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Employment type</span>
                                <span class="profile-kv-value"><?php echo e($employee->employmentType?->name ?? '—'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="profile-card h-100 mb-0">
                        <div class="profile-card-header">
                            <h2 class="profile-card-title">
                                <i class="fa-solid fa-file-contract text-primary"></i><span>Contract & tenure</span>
                            </h2>
                            <?php if(auth()->user()->can('employee.edit')): ?>
                                <a href="<?php echo e(route('employees.edit', $employee)); ?>" class="btn btn-action-link btn-sm">
                                    <i class="fa-solid fa-pen"></i><span>Edit</span>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="profile-card-body">
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Hire date</span>
                                <span class="profile-kv-value"><?php echo e($employee->hire_date?->format('d M Y') ?? '—'); ?></span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Probation end date</span>
                                <span class="profile-kv-value"><?php echo e($employee->probation_end_date?->format('d M Y') ?? '—'); ?></span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Contract start</span>
                                <span class="profile-kv-value"><?php echo e($employee->contract_start_date?->format('d M Y') ?? '—'); ?></span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Contract end</span>
                                <span class="profile-kv-value"><?php echo e($employee->contract_end_date?->format('d M Y') ?? 'Permanent / Ongoing'); ?></span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Tenure</span>
                                <span class="profile-kv-value"><?php echo e($employee->hire_date ? $employee->hire_date->diffForHumans(null, true) : '—'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Payroll Details -->
        <div class="tab-pane fade" id="tab-payroll" role="tabpanel" aria-labelledby="tab-payroll-btn">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="profile-card h-100 mb-0">
                        <div class="profile-card-header">
                            <h2 class="profile-card-title">
                                <i class="fa-solid fa-wallet text-primary"></i><span>Salary & compensation</span>
                            </h2>
                            <?php if(auth()->user()->can('employee.edit')): ?>
                                <a href="<?php echo e(route('employees.edit', $employee)); ?>" class="btn btn-action-link btn-sm">
                                    <i class="fa-solid fa-pen"></i><span>Edit</span>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="profile-card-body">
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Base salary</span>
                                <span class="profile-kv-value fs-6 text-primary fw-bold">
                                    <?php echo e($employee->base_salary ? number_format((float) $employee->base_salary, 2).' '.$employee->salary_currency : '—'); ?>

                                </span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Salary currency</span>
                                <span class="profile-kv-value font-monospace"><?php echo e($employee->salary_currency ?: 'USD'); ?></span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Payment method</span>
                                <span class="profile-kv-value"><?php echo e(str($employee->payment_method ?: 'Bank Transfer')->replace('_', ' ')->title()); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="profile-card h-100 mb-0">
                        <div class="profile-card-header">
                            <h2 class="profile-card-title">
                                <i class="fa-solid fa-building-columns text-primary"></i><span>Bank account details</span>
                            </h2>
                            <?php if(auth()->user()->can('employee.edit')): ?>
                                <a href="<?php echo e(route('employees.edit', $employee)); ?>" class="btn btn-action-link btn-sm">
                                    <i class="fa-solid fa-pen"></i><span>Edit</span>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="profile-card-body">
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Bank name</span>
                                <span class="profile-kv-value"><?php echo e($employee->bank_name ?: '—'); ?></span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Account holder name</span>
                                <span class="profile-kv-value"><?php echo e($employee->bank_account_name ?: '—'); ?></span>
                            </div>
                            <div class="profile-kv-row">
                                <span class="profile-kv-label">Account number</span>
                                <span class="profile-kv-value font-monospace fw-bold"><?php echo e($employee->bank_account_number ?: '—'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 4: Documents -->
        <div class="tab-pane fade" id="tab-documents" role="tabpanel" aria-labelledby="tab-documents-btn">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h2 class="h6 fw-bold text-dark mb-0"><i class="fa-solid fa-file-invoice text-primary me-2"></i>Employee Document Archive</h2>
                <a class="btn btn-action-link btn-sm" href="<?php echo e(route('employees.documents.index', $employee)); ?>">
                    <i class="fa-solid fa-file-arrow-up"></i><span>Manage documents</span>
                </a>
            </div>
            <div class="reference-list">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Document Title</th>
                                <th>Category</th>
                                <th>File Name</th>
                                <th>Size</th>
                                <th>Uploaded Date</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $employee->documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ps-3 fw-semibold text-dark">
                                        <i class="fa-regular fa-file-pdf text-danger me-2"></i><?php echo e($doc->title ?: $doc->original_name); ?>

                                    </td>
                                    <td><span class="badge text-bg-light border"><?php echo e(str($doc->document_type ?? 'General')->headline()); ?></span></td>
                                    <td class="small text-body-secondary font-monospace"><?php echo e($doc->original_name); ?></td>
                                    <td class="small"><?php echo e(number_format($doc->file_size / 1024, 1)); ?> KB</td>
                                    <td class="small"><?php echo e($doc->created_at->format('d M Y')); ?></td>
                                    <td class="text-end pe-3">
                                        <a class="btn btn-action-link btn-sm" href="<?php echo e(route('employees.documents.download', [$employee, $doc])); ?>" target="_blank">
                                            <i class="fa-solid fa-download"></i><span>Download</span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td class="text-center text-body-secondary py-5" colspan="6">
                                        <i class="fa-solid fa-file-arrow-up fa-xl d-block mb-3 text-primary"></i>No documents uploaded for this employee yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 5: Employment History -->
        <div class="tab-pane fade" id="tab-history" role="tabpanel" aria-labelledby="tab-history-btn">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h2 class="h6 fw-bold text-dark mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Employment Lifecycle & Career Progression</h2>
                <a class="btn btn-action-link btn-sm" href="<?php echo e(route('employees.history.index', $employee)); ?>">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><span>View full history</span>
                </a>
            </div>
            <div class="reference-list">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Effective Date</th>
                                <th>Event Type</th>
                                <th>Department & Position</th>
                                <th>Change Details / Remarks</th>
                                <th class="text-end pe-3">Logged By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $employee->employmentHistories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ps-3 fw-semibold text-dark"><?php echo e($history->effective_date?->format('d M Y') ?? '—'); ?></td>
                                    <td><span class="badge text-bg-primary"><?php echo e(str($history->event_type)->replace('_', ' ')->title()); ?></span></td>
                                    <td>
                                        <div><?php echo e($history->department?->name ?? '—'); ?></div>
                                        <small class="text-body-secondary"><?php echo e($history->position?->title ?? '—'); ?></small>
                                    </td>
                                    <td class="small"><?php echo e($history->change_reason ?: 'Regular organizational update'); ?></td>
                                    <td class="text-end pe-3 small text-body-secondary"><?php echo e($history->creator?->name ?? 'System'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td class="text-center text-body-secondary py-5" colspan="5">
                                        <i class="fa-solid fa-clock-rotate-left fa-xl d-block mb-3 text-primary"></i>No employment history logs found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Action -->
    <?php if(auth()->user()->can('employee.delete')): ?>
        <div class="mt-4 pt-3 border-top text-end">
            <form method="POST" action="<?php echo e(route('employees.destroy', $employee)); ?>" class="d-inline" data-confirm="Archive this employee? Existing payroll and attendance records remain available.">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button class="btn btn-sm btn-outline-danger" type="submit">
                    <i class="fa-solid fa-box-archive me-1"></i>Archive employee record
                </button>
            </form>
        </div>
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
<?php /**PATH D:\www\bizhr\resources\views\employees\show.blade.php ENDPATH**/ ?>