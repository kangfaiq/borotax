<div
    <?php echo e($attributes
            ->merge([
                'id' => $getId(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)); ?>

>
    <?php echo e($getChildSchema()); ?>

</div>
<?php /**PATH F:\Worx\laragon\www\borotax\vendor\filament\schemas\resources\views/components/grid.blade.php ENDPATH**/ ?>