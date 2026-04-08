<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Status Billing - Borotax')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary: #0f766e;
            --primary-rgb: 15, 118, 110;
            --primary-soft: rgba(15, 118, 110, 0.1);
            --primary-border: rgba(15, 118, 110, 0.18);
            --bg-body: #f4f7fb;
            --bg-card: #ffffff;
            --bg-surface-variant: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-tertiary: #94a3b8;
            --border: #e2e8f0;
            --border-light: #edf2f7;
            --shadow-sm: 0 8px 24px rgba(15, 23, 42, 0.06);
            --transition: 0.25s ease;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(var(--primary-rgb), 0.12), transparent 28%),
                linear-gradient(180deg, #f8fbfd 0%, var(--bg-body) 100%);
            color: var(--text-secondary);
        }

        .billing-status-shell {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
            padding: 40px 0 56px;
        }

        .billing-status-header {
            display: grid;
            gap: 10px;
            margin-bottom: 24px;
        }

        .billing-status-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(15, 118, 110, 0.12);
            color: var(--primary);
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .billing-status-header h1 {
            margin: 0;
            font-size: clamp(1.8rem, 2.4vw, 2.5rem);
            line-height: 1.15;
            color: var(--text-primary);
        }

        .billing-status-header p {
            margin: 0;
            max-width: 760px;
            line-height: 1.7;
        }

        .billing-status-panel {
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(255, 255, 255, 0.72);
            border-radius: 28px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(14px);
            padding: 28px;
        }

        @media (max-width: 768px) {
            .billing-status-shell {
                width: min(100% - 20px, 1120px);
                padding-top: 20px;
                padding-bottom: 28px;
            }

            .billing-status-panel {
                border-radius: 22px;
                padding: 18px;
            }
        }
    </style>

    @yield('styles')
</head>

<body>
    <main class="billing-status-shell">
        <div class="billing-status-header">
            <span class="billing-status-eyebrow">Status Billing Internal</span>
            <h1>@yield('page-title', 'Status Billing')</h1>
            <p>Halaman ini ditampilkan tanpa navigasi portal agar fokus pemeriksaan dokumen pembetulan tetap berada pada status billing yang sedang berlaku.</p>
        </div>

        <section class="billing-status-panel">
            @yield('content')
        </section>
    </main>
</body>

</html>