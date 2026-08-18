<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head><?php echo $__env->make('partials.head', ['title' => $title ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></head>
    <body class="app-body" hx-history="false">
    <?php echo $__env->make('partials.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main class="app-workspace px-4 px-lg-5 pt-3 pb-4"><?php echo e($slot); ?></main>
    <script nonce="<?php echo e(request()->attributes->get('csp_nonce')); ?>">window.appTablePreferences = <?php echo json_encode(auth()->user()?->table_preferences ?? [], 15, 512) ?>; window.tablePreferenceUrl = <?php echo json_encode(route('preferences.table-columns.update'), 15, 512) ?>;</script>
    <script src="<?php echo e(asset('vendor/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendor/htmx/htmx.min.js')); ?>"></script>
    <script nonce="<?php echo e(request()->attributes->get('csp_nonce')); ?>">htmx.config.historyCacheSize = 0; htmx.config.allowScriptTags = false; htmx.config.selfRequestsOnly = true; htmx.config.timeout = 15000;</script>
    <script src="<?php echo e(asset('js/app.js')); ?>"></script>
    <?php if(session('open_modal')): ?><script nonce="<?php echo e(request()->attributes->get('csp_nonce')); ?>">document.addEventListener('DOMContentLoaded', () => bootstrap.Modal.getOrCreateInstance(document.getElementById(<?php echo json_encode(session('open_modal'), 15, 512) ?>)).show());</script><?php endif; ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\www\bizhr\resources\views\layouts\app.blade.php ENDPATH**/ ?>