<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Dokumen BOROTAX</title>
    <style>
        :root {
            --bg: #f2efe8;
            --surface: rgba(255, 255, 255, 0.86);
            --surface-strong: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --line: rgba(31, 41, 55, 0.12);
            --primary: #0f766e;
            --primary-dark: #115e59;
            --accent: #b45309;
            --shadow: 0 18px 48px rgba(15, 23, 42, 0.12);
            --radius: 24px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Georgia, 'Times New Roman', serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(15, 118, 110, 0.14), transparent 34%),
                radial-gradient(circle at top right, rgba(180, 83, 9, 0.12), transparent 28%),
                linear-gradient(180deg, #f8f5ee 0%, var(--bg) 52%, #ebe5d8 100%);
            min-height: 100vh;
        }

        .shell {
            width: min(1180px, calc(100% - 32px));
            margin: 32px auto 48px;
        }

        .hero {
            padding: 28px;
            border: 1px solid var(--line);
            border-radius: calc(var(--radius) + 4px);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.92), rgba(255, 248, 240, 0.9));
            box-shadow: var(--shadow);
        }

        .eyebrow {
            margin: 0 0 10px;
            font-size: 0.75rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--primary-dark);
            font-weight: 700;
        }

        h1 {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3.25rem);
            line-height: 1.04;
            max-width: 780px;
        }

        .hero p {
            margin: 14px 0 0;
            max-width: 760px;
            font-size: 1rem;
            line-height: 1.65;
            color: var(--muted);
        }

        .info-strip {
            margin-top: 18px;
            display: inline-flex;
            gap: 10px;
            align-items: center;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(15, 118, 110, 0.08);
            color: var(--primary-dark);
            font-size: 0.92rem;
        }

        .group {
            margin-top: 28px;
        }

        .group h2 {
            margin: 0 0 14px;
            font-size: 1.5rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 18px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(10px);
        }

        .card h3 {
            margin: 0;
            font-size: 1.08rem;
        }

        .card p {
            margin: 10px 0 16px;
            color: var(--muted);
            line-height: 1.6;
            min-height: 76px;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 999px;
            font-size: 0.92rem;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid transparent;
            transition: transform 140ms ease, box-shadow 140ms ease, background 140ms ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 10px 24px rgba(15, 118, 110, 0.24);
        }

        .btn-secondary {
            background: var(--surface-strong);
            border-color: rgba(180, 83, 9, 0.18);
            color: var(--accent);
        }

        @media (max-width: 720px) {
            .shell {
                width: min(100% - 20px, 1180px);
                margin-top: 20px;
            }

            .hero,
            .card {
                border-radius: 20px;
            }

            .card p {
                min-height: 0;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        <section class="hero">
            <p class="eyebrow">Preview Lokal</p>
            <h1>Katalog dokumen cetak BOROTAX tanpa data database</h1>
            <p>
                Seluruh preview di halaman ini memakai fixture in-memory. Tidak ada insert ke tabel billing, SKPD, STPD,
                atau surat ketetapan. Gunakan ini untuk memeriksa layout, kerapian konten, dan konsistensi format PDF.
            </p>
            <div class="info-strip">Aktif hanya pada environment local/testing dan hanya untuk role backoffice.</div>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $previews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <section class="group">
                <h2><?php echo e($category); ?></h2>
                <div class="grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="card">
                            <h3><?php echo e($item['label']); ?></h3>
                            <p><?php echo e($item['description']); ?></p>
                            <div class="actions">
                                <a class="btn btn-primary" href="<?php echo e(route('document-previews.show', $item['slug'])); ?>" target="_blank" rel="noopener noreferrer">
                                    Buka Preview
                                </a>
                                <a class="btn btn-secondary" href="<?php echo e(route('document-previews.show', $item['slug'])); ?>?download=1">
                                    Unduh PDF
                                </a>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</body>
</html><?php /**PATH F:\Worx\laragon\www\borotax\resources\views/document-previews/index.blade.php ENDPATH**/ ?>