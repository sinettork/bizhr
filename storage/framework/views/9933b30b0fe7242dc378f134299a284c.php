<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Record attendance']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Record attendance']); ?><div class="row justify-content-center"><div class="col-lg-6"><div class="card border-0 shadow-sm"><div class="card-body p-4 text-center"><span class="metric-icon bg-primary-subtle text-primary mb-3"><i class="fa-solid fa-location-dot"></i></span><h1 class="h4">Record attendance</h1><p class="text-body-secondary">Branch: <strong><?php echo e($session->branch?->name); ?></strong><br>Allow precise location so the system can verify your attendance.</p><div id="attendanceQrStatus" class="alert alert-info">Requesting GPS location…</div><button id="attendanceQrRetry" class="btn btn-primary" type="button">Try again</button></div></div></div></div><?php $__env->startPush('scripts'); ?><script nonce="<?php echo e(request()->attributes->get('csp_nonce')); ?>">const status=document.getElementById('attendanceQrStatus'),retry=document.getElementById('attendanceQrRetry');async function record(){if(!navigator.geolocation){status.className='alert alert-danger';status.textContent='This browser does not support location.';return}status.className='alert alert-info';status.textContent='Getting precise location…';navigator.geolocation.getCurrentPosition(async p=>{try{const res=await fetch(<?php echo json_encode(route('attendance.qr.record', $token), 512) ?>,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({latitude:p.coords.latitude,longitude:p.coords.longitude,accuracy:p.coords.accuracy})});const data=await res.json();if(!res.ok)throw new Error(data.message||Object.values(data.errors||{}).flat().join(' '));status.className='alert alert-success';status.textContent=`${data.action === 'check_in' ? 'Checked in' : 'Checked out'} at ${data.time} · ${data.distance} m from ${data.branch}.`;retry.hidden=true}catch(e){status.className='alert alert-danger';status.textContent=e.message||'Unable to record attendance.'}},()=>{status.className='alert alert-danger';status.textContent='Location access is required. Enable precise location and try again.'},{enableHighAccuracy:true,timeout:20000,maximumAge:0})}retry.addEventListener('click',record);record();</script><?php $__env->stopPush(); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal81a506f898233b9e7d58286e6bea3c18)): ?>
<?php $attributes = $__attributesOriginal81a506f898233b9e7d58286e6bea3c18; ?>
<?php unset($__attributesOriginal81a506f898233b9e7d58286e6bea3c18); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal81a506f898233b9e7d58286e6bea3c18)): ?>
<?php $component = $__componentOriginal81a506f898233b9e7d58286e6bea3c18; ?>
<?php unset($__componentOriginal81a506f898233b9e7d58286e6bea3c18); ?>
<?php endif; ?>
<?php /**PATH D:\www\bizhr\resources\views\attendance\qr\verify.blade.php ENDPATH**/ ?>