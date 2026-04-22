@extends('layouts.portal-guest')

@section('title', 'Histori Pajak - Borotax')
@section('navbar-class', 'navbar-light')

@section('nav-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/histori-pajak') }}" style="color: var(--primary-dark); font-weight: 600;">Histori Pajak</a>
    <a href="{{ url('/login') }}" class="btn btn-primary btn-sm navbar-cta">Login</a>
@endsection

@section('nav-mobile-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/histori-pajak') }}">Histori Pajak</a>
    <a href="{{ url('/login') }}">Login Wajib Pajak</a>
@endsection

@section('styles')
<style>
    .histori-page { padding: 100px 0 60px; min-height: 100vh; }
    .histori-box {
        background: var(--bg-card); border-radius: var(--radius-xl);
        border: 1px solid var(--border); padding: 36px;
        max-width: 1200px; margin: 0 auto; box-shadow: var(--shadow-md);
    }
    .histori-box h2 { font-size: 1.5rem; font-weight: 800; margin-bottom: 8px; color: var(--text-primary); }
    .histori-box .subtitle { color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 28px; line-height: 1.6; }
    .form-grid { display: grid; gap: 14px; grid-template-columns: 2fr 1fr; }
    @media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }
    .form-input, .form-select {
        width: 100%; padding: 12px 16px;
        border: 1.5px solid var(--border); border-radius: var(--radius-md);
        font-size: 0.92rem; background: var(--bg-surface-variant);
        outline: none; transition: all var(--transition);
    }
    .form-input:focus, .form-select:focus {
        border-color: var(--primary); background: var(--bg-card);
        box-shadow: 0 0 0 3px rgba(108,172,207,0.15);
    }
    .form-label { display:block; font-size:0.82rem; font-weight:600; margin-bottom:6px; color: var(--text-primary); }
    .submit-row { display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-top:18px; }
    .alert-error {
        background: #fee2e2; color: #991b1b; padding: 12px 16px;
        border-radius: var(--radius-md); border: 1px solid #fca5a5;
        font-size: 0.88rem; margin-top: 14px;
    }
    .summary-cards {
        display: grid; gap: 14px; margin: 28px 0 18px;
        grid-template-columns: repeat(4, 1fr);
    }
    @media (max-width: 900px) { .summary-cards { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 480px) { .summary-cards { grid-template-columns: 1fr; } }
    .summary-card {
        background: var(--bg-surface-variant); border:1px solid var(--border);
        border-radius: var(--radius-md); padding: 16px;
    }
    .summary-card .label { font-size:0.75rem; color: var(--text-secondary); text-transform:uppercase; letter-spacing:.05em; }
    .summary-card .value { font-size:1.25rem; font-weight:800; color: var(--text-primary); margin-top:4px; }
    .summary-card.tagihan .value { color: var(--primary-dark); }
    .summary-card.terbayar .value { color: #16a34a; }
    .summary-card.tunggakan .value { color: #dc2626; }
    .actions-bar { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
    .btn-action {
        padding: 8px 14px; border-radius: var(--radius-md);
        font-size: 0.85rem; font-weight: 600; border: 1px solid var(--border);
        background: var(--bg-card); cursor: pointer; transition: all var(--transition);
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-action:hover { background: var(--bg-surface-variant); }
    .table-wrapper { overflow-x: auto; border-radius: var(--radius-md); border: 1px solid var(--border); }
    .histori-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .histori-table th, .histori-table td { padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--border); vertical-align: top; }
    .histori-table th { background: var(--bg-surface-variant); font-weight: 700; color: var(--text-primary); white-space: nowrap; }
    .histori-table tbody tr:hover { background: var(--bg-surface-variant); }
    .histori-table .text-right { text-align: right; white-space: nowrap; }
    .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; line-height: 1.4; }
    .badge-info    { background:#dbeafe; color:#1e40af; }
    .badge-warning { background:#fef3c7; color:#92400e; }
    .badge-success { background:#dcfce7; color:#166534; }
    .badge-purple  { background:#ede9fe; color:#5b21b6; }
    .badge-cyan    { background:#cffafe; color:#155e75; }
    .badge-pink    { background:#fce7f3; color:#9d174d; }
    .badge-gray    { background:#e5e7eb; color:#374151; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--text-secondary); }
    @media (max-width: 1100px) { .histori-table .col-terbayar { display: none; } }
    @media (max-width: 800px)  { .histori-table .col-tanggal-terbit, .histori-table .col-status   { display: none; } }
    .histori-table tr.row-overdue { background: #fff5f5; }
    .histori-table tr.row-overdue td { color: #b91c1c; }
    .histori-table .badge-overdue { display:inline-block; padding:2px 8px; border-radius:6px; background:#dc2626; color:#fff; font-weight:600; font-size:0.85em; }
</style>
@endsection

@section('content')
    <livewire:histori-pajak-public />
@endsection

@section('scripts')
@if(config('services.turnstile.key'))
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif
<script>
    window.addEventListener('turnstile-reset', () => {
        if (window.turnstile) { try { window.turnstile.reset(); } catch (e) {} }
    });
    function onTurnstileSuccess(token) {
        if (window.Livewire) { window.Livewire.dispatch('turnstile-success', { token }); }
    }
</script>
@endsection
