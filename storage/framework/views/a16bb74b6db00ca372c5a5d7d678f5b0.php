<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="grid gap-5">
        <section class="p-6 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-white to-emerald-50 dark:from-gray-800 dark:to-gray-800 shadow-lg">
            <div class="grid gap-2">
                <span class="text-xs font-bold tracking-widest uppercase text-teal-700 dark:text-teal-400">Admin Only</span>
                <h2 class="text-3xl font-bold leading-tight text-gray-900 dark:text-white">Preview semua dokumen tanpa insert database</h2>
                <p class="max-w-3xl text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    Halaman ini memakai fixture in-memory untuk merender seluruh dokumen PDF utama BOROTAX. Anda bisa membuka preview atau mengunduh PDF untuk mengecek layout dan isi dokumen tanpa menambah data operasional.
                </p>
            </div>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $previews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <section class="grid gap-3">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white"><?php echo e($category); ?></h3>
                <div style="display:grid; gap:16px; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="grid gap-4 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-md">
                            <div class="grid gap-2">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white"><?php echo e($item['label']); ?></h4>
                                <p class="text-sm leading-relaxed text-gray-500 dark:text-gray-400"><?php echo e($item['description']); ?></p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="<?php echo e(route('document-previews.show', $item['slug'])); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-teal-700 dark:bg-teal-600 text-white font-bold text-sm no-underline hover:bg-teal-800 dark:hover:bg-teal-500 transition-colors">
                                    Buka Preview
                                </a>
                                <a href="<?php echo e(route('document-previews.show', $item['slug'])); ?>?download=1" class="inline-flex items-center justify-center px-4 py-2 rounded-full border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 font-bold text-sm no-underline hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors">
                                    Unduh PDF
                                </a>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?><?php /**PATH F:\Worx\laragon\www\borotax\resources\views/filament/pages/preview-dokumen.blade.php ENDPATH**/ ?>