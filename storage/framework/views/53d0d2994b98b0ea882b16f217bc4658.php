<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Recruitment']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recruitment']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Recruitment &amp; Talent Acquisition','icon' => 'fa-user-plus']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recruitment &amp; Talent Acquisition','icon' => 'fa-user-plus']); ?>
         <?php $__env->slot('filters', null, []); ?> 
            <form class="reference-filter-form" method="GET" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search vacancies or candidates" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms">
                </div>
                <select class="form-select reference-status" name="status" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All statuses</option>
                    <?php $__currentLoopData = ['draft','open','closed','cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($status); ?>" <?php if(request('status') === $status): echo 'selected'; endif; ?>><?php echo e(str($status)->title()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button class="btn btn-primary reference-search-button">Filter</button>
            </form>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('recruitment.manage')): ?>
                <?php if (isset($component)) { $__componentOriginal32c85afa77cc4fec54802bf6365d2208 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal32c85afa77cc4fec54802bf6365d2208 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.list-actions','data' => ['addModal' => 'addVacancy','addLabel' => 'Open new vacancy']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('list-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['add-modal' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('addVacancy'),'add-label' => 'Open new vacancy']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal32c85afa77cc4fec54802bf6365d2208)): ?>
<?php $attributes = $__attributesOriginal32c85afa77cc4fec54802bf6365d2208; ?>
<?php unset($__attributesOriginal32c85afa77cc4fec54802bf6365d2208); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal32c85afa77cc4fec54802bf6365d2208)): ?>
<?php $component = $__componentOriginal32c85afa77cc4fec54802bf6365d2208; ?>
<?php unset($__componentOriginal32c85afa77cc4fec54802bf6365d2208); ?>
<?php endif; ?>
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
    <?php if($errors->any()): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span><?php echo e($errors->first()); ?></span></div>
    <?php endif; ?>

    <!-- Job Vacancies Table -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h2 class="h6 fw-bold text-dark mb-0"><i class="fa-solid fa-folder-open text-primary me-2"></i>Job Vacancies</h2>
        <span class="small text-body-secondary"><?php echo e($vacancies->total()); ?> vacancies</span>
    </div>

    <div class="reference-list mb-4" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Vacancy Title</th>
                        <th>Position &amp; Branch</th>
                        <th>Openings</th>
                        <th>Dates</th>
                        <th>Candidates</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $vacancies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vacancy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark"><?php echo e($vacancy->title); ?></div>
                            </td>
                            <td>
                                <div><?php echo e($vacancy->position?->title ?: 'General'); ?></div>
                                <small class="text-body-secondary"><?php echo e($vacancy->branch?->name ?: 'All branches'); ?></small>
                            </td>
                            <td><?php echo e($vacancy->openings); ?> opening(s)</td>
                            <td>
                                <?php echo e($vacancy->open_date?->format('d M Y')); ?> – <?php echo e($vacancy->close_date?->format('d M Y') ?: 'Open-ended'); ?>

                            </td>
                            <td>
                                <span class="fw-semibold text-primary"><i class="fa-solid fa-users me-1"></i><?php echo e($vacancy->applicants_count); ?> candidate(s)</span>
                            </td>
                            <td>
                                <span class="badge text-bg-<?php echo e($vacancy->status === 'open' ? 'success' : 'secondary'); ?>"><?php echo e(str($vacancy->status)->title()); ?></span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('recruitment.manage')): ?>
                                    <button class="btn btn-action-link btn-sm" data-bs-toggle="modal" data-bs-target="#addApplicant<?php echo e($vacancy->id); ?>">
                                        <i class="fa-solid fa-user-plus"></i><span>Add candidate</span>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td class="text-center text-body-secondary py-4" colspan="7">No job vacancies found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (isset($component)) { $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination-footer','data' => ['paginator' => $vacancies]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vacancies)]); ?>
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

    <!-- Candidate Pipeline Section -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h2 class="h6 fw-bold text-dark mb-0"><i class="fa-solid fa-diagram-project text-primary me-2"></i>Candidate Pipeline</h2>
        <span class="small text-body-secondary"><?php echo e($applicants->count()); ?> candidate(s)</span>
    </div>

    <div class="reference-list">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Candidate</th>
                        <th>Vacancy</th>
                        <th>Contact</th>
                        <th>Applied Date</th>
                        <th>Stage</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $applicants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $applicant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $stageColors = [
                                'applied' => 'secondary',
                                'screening' => 'info',
                                'shortlisted' => 'primary',
                                'interview' => 'warning',
                                'offer_pending' => 'dark',
                                'offered' => 'primary',
                                'accepted' => 'success',
                                'hired' => 'success',
                                'rejected' => 'danger',
                                'declined' => 'danger',
                            ];
                            $next = [
                                'applied' => ['screening', 'rejected'],
                                'screening' => ['shortlisted', 'rejected'],
                                'shortlisted' => ['interview', 'rejected'],
                                'interview' => ['offer_pending', 'rejected'],
                                'offer_pending' => ['offered', 'rejected'],
                                'offered' => ['accepted', 'declined'],
                                'accepted' => ['hired'],
                            ][$applicant->status] ?? [];
                        ?>
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark"><?php echo e($applicant->full_name); ?></div>
                                <?php if($applicant->hr_note): ?><small class="text-body-secondary"><i class="fa-solid fa-note-sticky text-warning me-1"></i><?php echo e(\Illuminate\Support\Str::limit($applicant->hr_note, 50)); ?></small><?php endif; ?>
                            </td>
                            <td><?php echo e($applicant->vacancy->title); ?></td>
                            <td>
                                <div><a href="mailto:<?php echo e($applicant->email); ?>" class="text-decoration-none text-body"><?php echo e($applicant->email); ?></a></div>
                                <small class="text-body-secondary"><?php echo e($applicant->phone); ?></small>
                            </td>
                            <td><?php echo e($applicant->applied_at?->format('d M Y') ?? '—'); ?></td>
                            <td>
                                <span class="badge text-bg-<?php echo e($stageColors[$applicant->status] ?? 'secondary'); ?>"><?php echo e(str($applicant->status)->replace('_', ' ')->title()); ?></span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                <?php if($applicant->cv_path): ?>
                                    <a class="btn btn-action-link btn-sm" href="<?php echo e(route('recruitment.applicants.cv', $applicant)); ?>" target="_blank">
                                        <i class="fa-solid fa-file-arrow-down"></i><span>CV</span>
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('recruitment.manage')): ?>
                                    <?php if(count($next) > 0): ?>
                                        <form class="d-inline-flex gap-1 align-items-center" method="POST" action="<?php echo e(route('recruitment.applicants.transition', $applicant)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <select class="form-select form-select-sm" name="status" style="width: auto; font-size: .75rem; padding: .2rem .5rem;" required>
                                                <option value="">Move stage...</option>
                                                <?php $__currentLoopData = $next; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($stage); ?>"><?php echo e(str($stage)->replace('_', ' ')->title()); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <button class="btn btn-sm btn-primary" type="submit" style="font-size: .75rem; padding: .2rem .55rem;">Go</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td class="text-center text-body-secondary py-4" colspan="6">No candidates found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals -->
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('recruitment.manage')): ?>
        <div class="modal fade" id="addVacancy" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="<?php echo e(route('recruitment.vacancies.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h2 class="modal-title fs-5"><i class="fa-solid fa-briefcase me-2 text-primary"></i>Open new vacancy</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input class="form-control" name="title" required placeholder="e.g. Senior Software Engineer">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Openings <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" name="openings" value="1" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Position</label>
                                <select class="form-select" name="position_id">
                                    <option value="">Unspecified</option>
                                    <?php $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($position->id); ?>"><?php echo e($position->title); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Branch</label>
                                <select class="form-select" name="branch_id">
                                    <option value="">All branches</option>
                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Open date</label>
                                <input class="form-control" type="date" name="open_date" value="<?php echo e(today()->toDateString()); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Close date</label>
                                <input class="form-control" type="date" name="close_date">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="description" minlength="20" rows="4" required placeholder="Provide role responsibilities, key requirements, qualifications..."></textarea>
                            </div>
                        </div>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['saveLabel' => 'Open vacancy']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['save-label' => 'Open vacancy']); ?>
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

        <?php $__currentLoopData = $vacancies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vacancy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="modal fade" id="addApplicant<?php echo e($vacancy->id); ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST" enctype="multipart/form-data" action="<?php echo e(route('recruitment.applicants.store', $vacancy)); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header">
                            <h2 class="modal-title fs-5"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Add candidate · <?php echo e($vacancy->title); ?></h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full name <span class="text-danger">*</span></label>
                                    <input class="form-control" name="full_name" required placeholder="Candidate full name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input class="form-control" type="email" name="email" required placeholder="candidate@example.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input class="form-control" name="phone" required placeholder="+855 ...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">CV / Resume (PDF, DOC)</label>
                                    <input class="form-control" type="file" name="cv" accept=".pdf,.doc,.docx">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">HR Evaluation Note</label>
                                    <textarea class="form-control" name="hr_note" placeholder="Initial impressions, salary expectation, source..."></textarea>
                                </div>
                            </div>
                        </div>
                        <?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['saveLabel' => 'Add candidate']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['save-label' => 'Add candidate']); ?>
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
<?php endif; ?><?php /**PATH D:\www\bizhr\resources\views\recruitment\index.blade.php ENDPATH**/ ?>