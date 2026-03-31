<div
    x-data="{}"
    x-show="$store.sidebar.isOpen"
    x-transition:enter="lg:transition lg:delay-100"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-cloak
    class="fi-sidebar-footer-collapse hidden lg:flex items-center px-4 py-3"
>
    <button
        type="button"
        x-on:click="$store.sidebar.close()"
        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition duration-75"
    >
        <span class="text-xs font-medium">Collapse</span>
        <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-o-chevron-double-left','class' => 'h-4 w-4 rtl:rotate-180']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-chevron-double-left','class' => 'h-4 w-4 rtl:rotate-180']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $attributes = $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $component = $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
    </button>
</div>
<?php /**PATH F:\Worx\laragon\www\borotax\resources\views/filament/partials/sidebar-footer-collapse.blade.php ENDPATH**/ ?>