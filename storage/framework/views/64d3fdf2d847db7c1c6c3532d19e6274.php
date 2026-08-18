<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['searchPlaceholder' => 'Search...', 'hasStatus' => false, 'statusOptions' => [], 'statusValue' => '', 'hxTarget' => '#list-content', 'hxSwap' => 'innerHTML']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['searchPlaceholder' => 'Search...', 'hasStatus' => false, 'statusOptions' => [], 'statusValue' => '', 'hxTarget' => '#list-content', 'hxSwap' => 'innerHTML']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<form 
    <?php echo e($attributes->merge(['class' => 'reference-filter-form', 'method' => 'GET'])); ?>

    <?php if($attributes->has('hx-get')): ?>
        <?php echo e($attributes); ?>

    <?php else: ?>
        hx-get="<?php echo e(request()->url()); ?>"
        hx-target="<?php echo e($hxTarget); ?>"
        hx-swap="<?php echo e($hxSwap); ?>"
        hx-trigger="submit, keyup[keyCode=='Enter'] from input[name='search'], change from select[name='status']"
    <?php endif; ?>
>
    <?php if (isset($component)) { $__componentOriginal1c4b45f62348de9b6fa41ee823d3fa96 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1c4b45f62348de9b6fa41ee823d3fa96 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.search-input','data' => ['placeholder' => $searchPlaceholder,'hxTarget' => $hxTarget,'hxSwap' => $hxSwap]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('search-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($searchPlaceholder),'hxTarget' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hxTarget),'hxSwap' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hxSwap)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1c4b45f62348de9b6fa41ee823d3fa96)): ?>
<?php $attributes = $__attributesOriginal1c4b45f62348de9b6fa41ee823d3fa96; ?>
<?php unset($__attributesOriginal1c4b45f62348de9b6fa41ee823d3fa96); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1c4b45f62348de9b6fa41ee823d3fa96)): ?>
<?php $component = $__componentOriginal1c4b45f62348de9b6fa41ee823d3fa96; ?>
<?php unset($__componentOriginal1c4b45f62348de9b6fa41ee823d3fa96); ?>
<?php endif; ?>
    
    <?php if($hasStatus && count($statusOptions) > 0): ?>
        <select class="form-select reference-status" name="status" hx-trigger="change" hx-target="<?php echo e($hxTarget); ?>" hx-swap="<?php echo e($hxSwap); ?>">
            <option value="">All statuses</option>
            <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($option); ?>" <?php if($statusValue === $option): echo 'selected'; endif; ?>>
                    <?php echo e(ucfirst(str_replace('_', ' ', $option))); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    <?php endif; ?>
    
    <?php echo e($slot); ?>

    
    <button class="btn btn-primary reference-search-button" type="submit">Search</button>
</form>
<?php /**PATH D:\www\bizhr\resources\views/components/filter-bar.blade.php ENDPATH**/ ?>