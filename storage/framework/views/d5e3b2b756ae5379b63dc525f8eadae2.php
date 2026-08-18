<?php if($paginator->hasPages()): ?>
    <nav aria-label="Pagination navigation" data-pagination-first="<?php echo e($paginator->firstItem()); ?>" data-pagination-last="<?php echo e($paginator->lastItem()); ?>" data-pagination-total="<?php echo e($paginator->total()); ?>" data-pagination-current="<?php echo e($paginator->currentPage()); ?>" data-pagination-pages="<?php echo e($paginator->lastPage()); ?>">
        <ul class="pagination pagination-sm mb-0">
            <?php if($paginator->onFirstPage()): ?>
                <li class="page-item disabled" aria-disabled="true"><span class="page-link" aria-hidden="true">‹</span></li>
            <?php else: ?>
                <li class="page-item"><a class="page-link" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" aria-label="Previous page">‹</a></li>
            <?php endif; ?>

            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(is_string($element)): ?><li class="page-item disabled" aria-disabled="true"><span class="page-link"><?php echo e($element); ?></span></li><?php endif; ?>
                <?php if(is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page === $paginator->currentPage()): ?><li class="page-item active" aria-current="page"><span class="page-link"><?php echo e($page); ?></span></li>
                        <?php else: ?><li class="page-item"><a class="page-link" href="<?php echo e($url); ?>" aria-label="Go to page <?php echo e($page); ?>"><?php echo e($page); ?></a></li><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($paginator->hasMorePages()): ?>
                <li class="page-item"><a class="page-link" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" aria-label="Next page">›</a></li>
            <?php else: ?>
                <li class="page-item disabled" aria-disabled="true"><span class="page-link" aria-hidden="true">›</span></li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
<?php /**PATH D:\www\bizhr\resources\views/vendor/pagination/bizhr.blade.php ENDPATH**/ ?>