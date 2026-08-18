<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Payroll Periods']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Payroll Periods']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Payroll Cycles &amp; Disbursements','icon' => 'fa-money-check-dollar','context' => 'Payroll &amp; Compensation']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Payroll Cycles &amp; Disbursements','icon' => 'fa-money-check-dollar','context' => 'Payroll &amp; Compensation']); ?>
         <?php $__env->slot('filters', null, []); ?> 
            <form class="reference-filter-form" method="GET" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search payroll period..." hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms">
                </div>
                <select class="form-select reference-status" name="status" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All statuses</option>
                    <?php $__currentLoopData = ['draft','processing','awaiting_approval','approved','paid','closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($status); ?>" <?php if(request('status') === $status): echo 'selected'; endif; ?>><?php echo e(str($status)->replace('_',' ')->title()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button class="btn btn-primary reference-search-button">Filter</button>
            </form>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal32c85afa77cc4fec54802bf6365d2208 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal32c85afa77cc4fec54802bf6365d2208 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.list-actions','data' => ['addModal' => auth()->user()->can('payroll.edit') ? 'createPeriod' : null,'addLabel' => 'Create payroll period']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('list-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['add-modal' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user()->can('payroll.edit') ? 'createPeriod' : null),'add-label' => 'Create payroll period']); ?>
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

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Period Name</th>
                        <th>Dates</th>
                        <th>Payment Date</th>
                        <th>Employees</th>
                        <th>Total Net Payout</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $statusBadge = match($period->status) {
                                'paid' => 'success',
                                'approved' => 'primary',
                                'awaiting_approval' => 'warning',
                                'processing', 'draft' => 'info',
                                default => 'secondary',
                            };
                        ?>
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark"><?php echo e($period->name); ?></div>
                            </td>
                            <td><?php echo e($period->start_date->format('d/m/Y')); ?> – <?php echo e($period->end_date->format('d/m/Y')); ?></td>
                            <td><?php echo e($period->payment_date?->format('d M Y') ?? '—'); ?></td>
                            <td><?php echo e($period->items_count); ?> employee(s)</td>
                            <td class="fw-bold text-dark">$<?php echo e(number_format((float)$period->items_sum_net_salary, 2)); ?></td>
                            <td><span class="badge text-bg-<?php echo e($statusBadge); ?>"><?php echo e(str($period->status)->replace('_',' ')->title()); ?></span></td>
                            <td class="text-end pe-3 text-nowrap">
                                <?php if(auth()->user()->can('payroll.process') && in_array($period->status, ['draft','processing'])): ?>
                                    <form method="POST" action="<?php echo e(route('payroll.periods.generate', $period)); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button class="btn btn-action-link btn-sm" type="submit">
                                            <i class="fa-solid fa-calculator"></i><span>Generate</span>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if(auth()->user()->can('payroll.approve') && $period->status === 'awaiting_approval'): ?>
                                    <form method="POST" action="<?php echo e(route('payroll.periods.approve', $period)); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button class="btn btn-action-link btn-sm text-success" type="submit">
                                            <i class="fa-solid fa-check"></i><span>Approve</span>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if(auth()->user()->can('payroll.process') && $period->status === 'approved'): ?>
                                    <button class="btn btn-action-link btn-sm text-primary" type="button" data-bs-toggle="modal" data-bs-target="#pay<?php echo e($period->id); ?>">
                                        <i class="fa-solid fa-money-bill-transfer"></i><span>Record Payment</span>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="7">
                                <i class="fa-solid fa-money-check-dollar fa-xl d-block mb-3 text-primary"></i>No payroll periods recorded. Click "Create payroll period" to start a new cycle.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (isset($component)) { $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination-footer','data' => ['paginator' => $periods]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($periods)]); ?>
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

    <?php if(auth()->user()->can('payroll.edit')): ?>
        <div class="modal fade" id="createPeriod" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <form class="modal-content" method="POST" action="<?php echo e(route('payroll.periods.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h2 class="modal-title fs-5"><i class="fa-solid fa-money-check-dollar me-2 text-primary"></i>Create payroll period</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Period Name <span class="text-danger">*</span></label>
                            <input class="form-control" name="name" required value="Payroll <?php echo e(now()->format('m/Y')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start date <span class="text-danger">*</span></label>
                            <input class="form-control" type="date" name="start_date" required value="<?php echo e(now()->startOfMonth()->toDateString()); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End date <span class="text-danger">*</span></label>
                            <input class="form-control" type="date" name="end_date" required value="<?php echo e(now()->endOfMonth()->toDateString()); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Payment date</label>
                            <input class="form-control" type="date" name="payment_date" value="<?php echo e(now()->endOfMonth()->toDateString()); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tax exchange rate (KHR/USD) <span class="text-danger">*</span></label>
                            <input class="form-control" type="number" step="0.01" min="1" name="tax_exchange_rate_khr" required value="<?php echo e(\App\Models\PayrollSetting::forCompany(\App\Models\Company::query()->value('id'))->khr_per_usd); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Rate date <span class="text-danger">*</span></label>
                            <input class="form-control" type="date" name="tax_rate_date" required value="<?php echo e(now()->toDateString()); ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Rate source <span class="text-danger">*</span></label>
                            <input class="form-control" type="url" name="tax_rate_source" required value="https://www.tax.gov.kh/en/exchange-rate">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes &amp; Guidelines</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Optional internal payroll remarks..."></textarea>
                        </div>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['saveLabel' => 'Create payroll period']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['save-label' => 'Create payroll period']); ?>
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

    <?php if(auth()->user()->can('payroll.process')): ?>
        <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($period->status === 'approved'): ?>
                <div class="modal fade" id="pay<?php echo e($period->id); ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form class="modal-content" method="POST" action="<?php echo e(route('payroll.periods.pay', $period)); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="modal-header">
                                <h2 class="modal-title fs-5"><i class="fa-solid fa-money-bill-transfer me-2 text-primary"></i>Record payment · <?php echo e($period->name); ?></h2>
                                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label">Payment Method</label>
                                <select class="form-select" name="payment_method">
                                    <option value="bank_transfer">Bank transfer</option>
                                    <option value="cash">Cash</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="mobile_banking">Mobile banking</option>
                                    <option value="other">Other</option>
                                </select>
                                <label class="form-label mt-3">Disbursed timestamp <span class="text-danger">*</span></label>
                                <input class="form-control" type="datetime-local" name="paid_at" required value="<?php echo e(now()->format('Y-m-d\TH:i')); ?>">
                                <label class="form-label mt-3">Reference Number / Transaction ID</label>
                                <input class="form-control" name="reference_number" placeholder="Bank ref #">
                                <label class="form-label mt-3">Notes</label>
                                <textarea class="form-control" name="notes" placeholder="Disbursement remarks..."></textarea>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary" type="submit">Record payment</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
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
<?php endif; ?><?php /**PATH D:\www\bizhr\resources\views/payroll/periods/index.blade.php ENDPATH**/ ?>