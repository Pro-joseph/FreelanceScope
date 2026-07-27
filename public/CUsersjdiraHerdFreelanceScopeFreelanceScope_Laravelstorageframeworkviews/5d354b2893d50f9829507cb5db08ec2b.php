<div
    class="relative text-neutral-400 dark:text-neutral-400"
    x-data="{ spotlight: { x: 0, y: 0 } }"
    @mousemove="const rect = $el.getBoundingClientRect(); spotlight = { x: $event.clientX - rect.left, y: $event.clientY - rect.top }">
    <div
        class="absolute w-full text-neutral-800 dark:text-neutral-100"
        x-data="{ isDark: window.matchMedia('(prefers-color-scheme: dark)').matches || document.documentElement.classList.contains('dark') }"
        :style="
            'mask-image: radial-gradient(circle at ' +
                spotlight.x +
                'px ' +
                spotlight.y +
                'px, black 0%, transparent ' + (isDark ? '150px' : '120px') + '); -webkit-mask-image: radial-gradient(circle at ' +
                spotlight.x +
                'px ' +
                spotlight.y +
                'px, black 0%, transparent ' + (isDark ? '600px' : '400px') + ');'
        ">
        <?php
use Illuminate\View\AnonymousComponent;
        use Illuminate\View\ComponentAttributeBag;

        if (isset($component)) {
            $__componentOriginal2a45ee13943eadc15ee63d255f492356 = $component;
        } ?>
<?php if (isset($attributes)) {
            $__attributesOriginal2a45ee13943eadc15ee63d255f492356 = $attributes;
        } ?>
<?php $component = AnonymousComponent::resolve(['view' => 'laravel-exceptions-renderer::components.icons.laravel-ascii', 'data' => []] + (isset($attributes) && $attributes instanceof ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('laravel-exceptions-renderer::icons.laravel-ascii'); ?>
<?php if ($component->shouldRender()) { ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof ComponentAttributeBag) { ?>
<?php $attributes = $attributes->except(AnonymousComponent::ignoredParameterNames()); ?>
<?php } ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php } ?>
<?php if (isset($__attributesOriginal2a45ee13943eadc15ee63d255f492356)) { ?>
<?php $attributes = $__attributesOriginal2a45ee13943eadc15ee63d255f492356; ?>
<?php unset($__attributesOriginal2a45ee13943eadc15ee63d255f492356); ?>
<?php } ?>
<?php if (isset($__componentOriginal2a45ee13943eadc15ee63d255f492356)) { ?>
<?php $component = $__componentOriginal2a45ee13943eadc15ee63d255f492356; ?>
<?php unset($__componentOriginal2a45ee13943eadc15ee63d255f492356); ?>
<?php } ?>
    </div>
    <?php if (isset($component)) {
        $__componentOriginal2a45ee13943eadc15ee63d255f492356 = $component;
    } ?>
<?php if (isset($attributes)) {
        $__attributesOriginal2a45ee13943eadc15ee63d255f492356 = $attributes;
    } ?>
<?php $component = AnonymousComponent::resolve(['view' => 'laravel-exceptions-renderer::components.icons.laravel-ascii', 'data' => []] + (isset($attributes) && $attributes instanceof ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('laravel-exceptions-renderer::icons.laravel-ascii'); ?>
<?php if ($component->shouldRender()) { ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof ComponentAttributeBag) { ?>
<?php $attributes = $attributes->except(AnonymousComponent::ignoredParameterNames()); ?>
<?php } ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php } ?>
<?php if (isset($__attributesOriginal2a45ee13943eadc15ee63d255f492356)) { ?>
<?php $attributes = $__attributesOriginal2a45ee13943eadc15ee63d255f492356; ?>
<?php unset($__attributesOriginal2a45ee13943eadc15ee63d255f492356); ?>
<?php } ?>
<?php if (isset($__componentOriginal2a45ee13943eadc15ee63d255f492356)) { ?>
<?php $component = $__componentOriginal2a45ee13943eadc15ee63d255f492356; ?>
<?php unset($__componentOriginal2a45ee13943eadc15ee63d255f492356); ?>
<?php } ?>
</div>
<?php /**PATH /var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Providers/../resources/exceptions/renderer/components/laravel-ascii-spotlight.blade.php ENDPATH**/ ?>