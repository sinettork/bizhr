<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Attendance QR']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Attendance QR']); ?><?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Branch attendance QR','icon' => 'fa-qrcode']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Branch attendance QR','icon' => 'fa-qrcode']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890)): ?>
<?php $attributes = $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890; ?>
<?php unset($__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890)): ?>
<?php $component = $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890; ?>
<?php unset($__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890); ?>
<?php endif; ?><?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>
<div class="card border-0 shadow-sm"><form method="POST" action="<?php echo e(route('attendance.qr.sessions.store')); ?>"><?php echo csrf_field(); ?><div class="card-body row g-3 align-items-end"><div class="col-md-7"><label class="form-label">Branch <span class="text-danger">*</span></label><select class="form-select" name="branch_id" required><option value="">Choose branch</option><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?> (<?php echo e($branch->code); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="col-md-3"><label class="form-label">QR lifetime</label><select class="form-select" name="lifetime_seconds"><option value="45">45 seconds</option><option value="60">60 seconds</option><option value="90">90 seconds</option></select></div><div class="col-md-2"><button class="btn btn-primary w-100"><i class="fa-solid fa-qrcode me-1"></i>Generate</button></div></div></form></div>
<?php if(session('qr_session')): ?>
    <?php ($qr = session('qr_session')); ?>
    <div class="card border-0 shadow-sm mt-3"><div class="card-body text-center"><h2 class="h5"><?php echo e($qr['branch']); ?></h2><p class="text-body-secondary">This QR expires at <?php echo e(\Carbon\Carbon::parse($qr['expires_at'])->timezone('Asia/Phnom_Penh')->format('H:i:s')); ?>. Display it only at the branch.</p><canvas id="attendanceQrCanvas" class="border rounded p-2 bg-white"></canvas><div class="mt-3"><a class="btn btn-outline-primary btn-sm" href="<?php echo e($qr['url']); ?>" target="_blank">Open attendance link</a></div></div></div>
    <?php $__env->startPush('scripts'); ?>
        <?php echo app('Illuminate\Foundation\Vite')('resources/js/qrcode-display.js'); ?>
        <script nonce="<?php echo e(request()->attributes->get('csp_nonce')); ?>">window.addEventListener('qrcode:ready', () => QRCode.toCanvas(document.getElementById('attendanceQrCanvas'), <?php echo json_encode($qr['url'], 15, 512) ?>, {width: 300, margin: 2, errorCorrectionLevel: 'M'}));</script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH D:\www\bizhr\resources\views/attendance/qr/display.blade.php ENDPATH**/ ?>