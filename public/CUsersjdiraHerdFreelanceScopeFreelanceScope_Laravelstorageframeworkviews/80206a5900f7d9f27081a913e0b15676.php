<?php
use Illuminate\View\AnonymousComponent;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\ComponentAttributeBag;

$attributes ??= new ComponentAttributeBag;

$__newAttributes = [];
$__propNames = ComponentAttributeBag::extractPropNames((['frame']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['frame']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) {
        unset($$__key);
    }
}

unset($__defined_vars, $__key, $__value); ?>

<div class="grid gap-3 p-4 bg-neutral-50 dark:bg-transparent overflow-x-auto rounded-lg">
    <?php if ($frame->previous()) { ?>
        <div class="flex">
            <?php if (isset($component)) {
                $__componentOriginalc33171fb5f34409a0ad661ae1625dcb2 = $component;
            } ?>
<?php if (isset($attributes)) {
                $__attributesOriginalc33171fb5f34409a0ad661ae1625dcb2 = $attributes;
            } ?>
<?php $component = AnonymousComponent::resolve(['view' => 'laravel-exceptions-renderer::components.formatted-source', 'data' => ['frame' => $frame, 'className' => 'text-xs']] + (isset($attributes) && $attributes instanceof ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('laravel-exceptions-renderer::formatted-source'); ?>
<?php if ($component->shouldRender()) { ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof ComponentAttributeBag) { ?>
<?php $attributes = $attributes->except(AnonymousComponent::ignoredParameterNames()); ?>
<?php } ?>
<?php $component->withAttributes(['frame' => BladeCompiler::sanitizeComponentAttribute($frame), 'className' => 'text-xs']); ?>
<?php echo $__env->renderComponent(); ?>
<?php } ?>
<?php if (isset($__attributesOriginalc33171fb5f34409a0ad661ae1625dcb2)) { ?>
<?php $attributes = $__attributesOriginalc33171fb5f34409a0ad661ae1625dcb2; ?>
<?php unset($__attributesOriginalc33171fb5f34409a0ad661ae1625dcb2); ?>
<?php } ?>
<?php if (isset($__componentOriginalc33171fb5f34409a0ad661ae1625dcb2)) { ?>
<?php $component = $__componentOriginalc33171fb5f34409a0ad661ae1625dcb2; ?>
<?php unset($__componentOriginalc33171fb5f34409a0ad661ae1625dcb2); ?>
<?php } ?>
        </div>
    <?php } else { ?>
        <span class="font-mono text-xs leading-3 text-neutral-500">Entrypoint</span>
    <?php } ?>

    <?php if (isset($component)) {
        $__componentOriginalfe2bc8d0a6d110d41fdc8740012cee8d = $component;
    } ?>
<?php if (isset($attributes)) {
        $__attributesOriginalfe2bc8d0a6d110d41fdc8740012cee8d = $attributes;
    } ?>
<?php $component = AnonymousComponent::resolve(['view' => 'laravel-exceptions-renderer::components.file-with-line', 'data' => ['frame' => $frame, 'class' => 'text-xs']] + (isset($attributes) && $attributes instanceof ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('laravel-exceptions-renderer::file-with-line'); ?>
<?php if ($component->shouldRender()) { ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof ComponentAttributeBag) { ?>
<?php $attributes = $attributes->except(AnonymousComponent::ignoredParameterNames()); ?>
<?php } ?>
<?php $component->withAttributes(['frame' => BladeCompiler::sanitizeComponentAttribute($frame), 'class' => 'text-xs']); ?>
<?php echo $__env->renderComponent(); ?>
<?php } ?>
<?php if (isset($__attributesOriginalfe2bc8d0a6d110d41fdc8740012cee8d)) { ?>
<?php $attributes = $__attributesOriginalfe2bc8d0a6d110d41fdc8740012cee8d; ?>
<?php unset($__attributesOriginalfe2bc8d0a6d110d41fdc8740012cee8d); ?>
<?php } ?>
<?php if (isset($__componentOriginalfe2bc8d0a6d110d41fdc8740012cee8d)) { ?>
<?php $component = $__componentOriginalfe2bc8d0a6d110d41fdc8740012cee8d; ?>
<?php unset($__componentOriginalfe2bc8d0a6d110d41fdc8740012cee8d); ?>
<?php } ?>
</div>
<?php /**PATH /var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Providers/../resources/exceptions/renderer/components/vendor-frame.blade.php ENDPATH**/ ?>