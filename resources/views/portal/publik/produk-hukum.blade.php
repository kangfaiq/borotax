@extends('layouts.portal-guest')

@section('title', 'Produk Hukum - Borotax')

@section('navbar-class', 'navbar-light')

@section('nav-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/produk-hukum') }}" style="color: var(--primary-dark); font-weight: 600;">Produk Hukum</a>
    <a href="{{ url('/login') }}" class="btn btn-primary btn-sm navbar-cta">Login</a>
@endsection

@section('nav-mobile-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/produk-hukum') }}">Produk Hukum</a>
    <a href="{{ url('/login') }}">Login Wajib Pajak</a>
@endsection

@section('styles')
<style>
.legal-page { padding: 100px 0 60px; min-height: 100vh; }

.legal-header { text-align: center; margin-bottom: 48px; }
.legal-header h1 { font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; }
.legal-header p { color: var(--text-secondary); font-size: 0.95rem; max-width: 600px; margin: 0 auto; }

.legal-list { max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: 12px; }

.legal-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 20px 24px;
    transition: all var(--transition);
    text-decoration: none;
    color: inherit;
}
.legal-item:hover {
    border-color: var(--primary-light);
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.legal-icon {
    flex-shrink: 0;
    width: 48px; height: 48px;
    border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
}

.legal-icon.uu { background: #EDE9FE; color: #7C3AED; }
.legal-icon.pp { background: #DBEAFE; color: #2563EB; }
.legal-icon.pmk { background: #D1FAE5; color: #059669; }
.legal-icon.pergub { background: #FEF3C7; color: #D97706; }
.legal-icon.kepgub { background: #FFE4E6; color: #E11D48; }
.legal-icon.perda { background: #E0F2FE; color: #0284C7; }
.legal-icon.perbup { background: #F3E8FF; color: #9333EA; }

.legal-content { flex: 1; min-width: 0; }
.legal-category {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 3px 10px;
    border-radius: var(--radius-full);
    margin-bottom: 6px;
}
.legal-category.uu { background: #EDE9FE; color: #7C3AED; }
.legal-category.pp { background: #DBEAFE; color: #2563EB; }
.legal-category.pmk { background: #D1FAE5; color: #059669; }
.legal-category.pergub { background: #FEF3C7; color: #D97706; }
.legal-category.kepgub { background: #FFE4E6; color: #E11D48; }
.legal-category.perda { background: #E0F2FE; color: #0284C7; }
.legal-category.perbup { background: #F3E8FF; color: #9333EA; }

.legal-title { font-size: 0.9rem; font-weight: 600; color: var(--text-primary); line-height: 1.5; margin-bottom: 4px; }
.legal-year { font-size: 0.8rem; color: var(--text-tertiary); }

.legal-open { flex-shrink: 0; color: var(--text-tertiary); font-size: 1.1rem; align-self: center; transition: color var(--transition); }
.legal-item:hover .legal-open { color: var(--primary); }

.publik-nav { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-bottom: 40px; }
.publik-nav a {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: var(--radius-full);
    font-size: 0.82rem; font-weight: 600; border: 1px solid var(--border);
    color: var(--text-secondary); background: var(--bg-card);
    transition: all var(--transition); text-decoration: none;
}
.publik-nav a:hover { border-color: var(--primary); color: var(--primary); }
.publik-nav a.active { background: var(--primary); color: white; border-color: var(--primary); }

@media (max-width: 768px) {
    .legal-item { padding: 16px; gap: 12px; }
    .legal-icon { width: 40px; height: 40px; font-size: 1.1rem; }
    .legal-title { font-size: 0.85rem; }
}
</style>
@endsection

@section('content')
<section class="legal-page">
    <div class="container">
        <div class="legal-header">
            <span class="section-badge"><i class="bi bi-bank"></i> LAYANAN PUBLIK</span>
            <h1>Produk Hukum</h1>
            <p>Daftar peraturan dan produk hukum terkait pajak daerah Kabupaten Bojonegoro</p>
        </div>

        {{-- Sub-navigation menu publik --}}
        @include('portal.publik._nav', ['active' => 'produk-hukum'])

        <div class="legal-list">
            @foreach($products as $product)
                @php
                    $cat = strtolower($product['category']);
                    if (str_contains($cat, 'undang-undang')) $cls = 'uu';
                    elseif (str_contains($cat, 'peraturan pemerintah')) $cls = 'pp';
                    elseif (str_contains($cat, 'menteri')) $cls = 'pmk';
                    elseif (str_contains($cat, 'gubernur') && str_contains($cat, 'keputusan')) $cls = 'kepgub';
                    elseif (str_contains($cat, 'gubernur')) $cls = 'pergub';
                    elseif (str_contains($cat, 'peraturan daerah')) $cls = 'perda';
                    elseif (str_contains($cat, 'bupati')) $cls = 'perbup';
                    else $cls = 'pp';
                @endphp
                <a href="{{ $product['url'] }}" target="_blank" rel="noopener" class="legal-item">
                    <div class="legal-icon {{ $cls }}">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="legal-content">
                        <span class="legal-category {{ $cls }}">{{ $product['category'] }}</span>
                        <div class="legal-title">{{ $product['title'] }}</div>
                        <div class="legal-year"><i class="bi bi-calendar3"></i> Tahun {{ $product['year'] }}</div>
                    </div>
                    <div class="legal-open"><i class="bi bi-box-arrow-up-right"></i></div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endsection
