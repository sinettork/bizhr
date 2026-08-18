<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<meta name="theme-color" content="#243a8f">
<title><?php echo e(filled($title ?? null) ? $title.' · '.config('app.name', 'BizHR') : config('app.name', 'BizHR')); ?></title>
<link rel="icon" href="<?php echo e(asset('images/Artboard 5 copy.png')); ?>" type="image/png">
<link href="<?php echo e(asset('vendor/bootstrap/css/bootstrap.min.css')); ?>" rel="stylesheet">
<link href="<?php echo e(asset('vendor/fontawesome/css/all.min.css')); ?>" rel="stylesheet">
<link href="<?php echo e(asset('css/app.css')); ?>" rel="stylesheet">
<?php /**PATH D:\www\bizhr\resources\views/partials/head.blade.php ENDPATH**/ ?>