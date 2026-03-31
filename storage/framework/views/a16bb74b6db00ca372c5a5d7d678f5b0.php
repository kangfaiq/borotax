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
    <div style="display:grid; gap:18px;">
        <section style="padding:24px; border:1px solid rgba(15, 23, 42, 0.08); border-radius:20px; background:linear-gradient(135deg, rgba(255,255,255,0.96), rgba(236,253,245,0.92)); box-shadow:0 18px 40px rgba(15,23,42,0.08);">
            <div style="display:grid; gap:10px;">
                <span style="font-size:0.78rem; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; color:#0f766e;">Admin Only</span>
                <h2 style="margin:0; font-size:2rem; line-height:1.1; color:#111827;">Preview semua dokumen tanpa insert database</h2>
                <p style="margin:0; max-width:760px; font-size:0.98rem; line-height:1.7; color:#4b5563;">
                    Halaman ini memakai fixture in-memory untuk merender seluruh dokumen PDF utama BOROTAX. Anda bisa membuka preview atau mengunduh PDF untuk mengecek layout dan isi dokumen tanpa menambah data operasional.
                </p>
            </div>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $previews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <section style="display:grid; gap:12px;">
                <h3 style="margin:0; font-size:1.25rem; color:#111827;"><?php echo e($category); ?></h3>
                <div style="display:grid; gap:16px; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article style="display:grid; gap:14px; padding:18px; border:1px solid rgba(15, 23, 42, 0.08); border-radius:18px; background:#fff; box-shadow:0 10px 28px rgba(15,23,42,0.06);">
                            <div style="display:grid; gap:8px;">
                                <h4 style="margin:0; font-size:1rem; color:#111827;"><?php echo e($item['label']); ?></h4>
                                <p style="margin:0; color:#6b7280; font-size:0.92rem; line-height:1.65;"><?php echo e($item['description']); ?></p>
                            </div>
                            <div style="display:flex; flex-wrap:wrap; gap:10px;">
                                <a href="<?php echo e(route('document-previews.show', $item['slug'])); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex; align-items:center; justify-content:center; padding:10px 14px; border-radius:999px; background:#0f766e; color:#fff; text-decoration:none; font-weight:700; font-size:0.9rem;">
                                    Buka Preview
                                </a>
                                <a href="<?php echo e(route('document-previews.show', $item['slug'])); ?>?download=1" style="display:inline-flex; align-items:center; justify-content:center; padding:10px 14px; border-radius:999px; background:#fff7ed; color:#b45309; text-decoration:none; font-weight:700; font-size:0.9rem; border:1px solid rgba(180, 83, 9, 0.18);">
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