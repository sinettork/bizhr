<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head><?php echo $__env->make('partials.head', ['title' => $title ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></head>
<body class="auth-body">
    <main class="container d-flex align-items-center min-vh-100"><div class="row justify-content-center w-100"><div class="col-sm-10 col-md-7 col-lg-5"><div class="card shadow-lg border-0"><div class="card-body p-4 p-md-5"><div class="auth-brand text-center mb-4"><span class="brand-mark"><i class="fa-solid fa-people-group"></i></span><span class="fs-4 fw-bold ms-2"><?php echo e(config('app.name', 'BizHR')); ?></span></div><?php echo e($slot); ?></div></div></div></div></main>
    <script src="<?php echo e(asset('vendor/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
</body>
</html>
<?php /**PATH D:\www\bizhr\resources\views\layouts\auth.blade.php ENDPATH**/ ?>