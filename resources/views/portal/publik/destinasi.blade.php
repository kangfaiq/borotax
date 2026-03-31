@extends('layouts.portal-guest')

@section('title', 'Destinasi Wisata Bojonegoro - Borotax')

@section('navbar-class', 'navbar-light')

@section('nav-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/destinasi') }}" style="color: var(--primary-dark); font-weight: 600;">Destinasi</a>
    <a href="{{ url('/login') }}" class="btn btn-primary btn-sm navbar-cta">Login</a>
@endsection

@section('nav-mobile-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/destinasi') }}">Destinasi</a>
    <a href="{{ url('/login') }}">Login Wajib Pajak</a>
@endsection

@section('styles')
<style>
.dest-page { padding: 100px 0 60px; min-height: 100vh; }

.dest-header { text-align: center; margin-bottom: 40px; }
.dest-header h1 { font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; }
.dest-header p { color: var(--text-secondary); font-size: 0.95rem; max-width: 600px; margin: 0 auto; }

/* Category Filter Tabs */
.dest-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
    margin-bottom: 40px;
}
.dest-filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 20px;
    border-radius: var(--radius-full);
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid var(--border);
    background: var(--bg-card);
    color: var(--text-secondary);
    transition: all var(--transition);
}
.dest-filter-btn:hover {
    border-color: var(--primary-light);
    color: var(--primary-dark);
    background: var(--primary-50);
}
.dest-filter-btn.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}

/* Grid */
.dest-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}

/* Card */
.dest-card {
    background: var(--bg-card);
    border-radius: var(--radius-xl);
    border: 1px solid var(--border);
    overflow: hidden;
    transition: all var(--transition);
    position: relative;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
}
.dest-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-xl);
    border-color: transparent;
}

.dest-card-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    background: linear-gradient(135deg, var(--primary-50) 0%, var(--primary-lighter) 100%);
}
.dest-card-img-placeholder {
    width: 100%;
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: var(--primary);
    background: linear-gradient(135deg, var(--primary-50) 0%, var(--primary-lighter) 100%);
}

.dest-card-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: var(--radius-full);
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.dest-card-badge.cat-wisata { background: #16a34a; }
.dest-card-badge.cat-kuliner { background: #ea580c; }
.dest-card-badge.cat-hotel { background: #2563eb; }
.dest-card-badge.cat-oleh-oleh { background: #7c3aed; }
.dest-card-badge.cat-hiburan { background: #dc2626; }

.dest-card-body {
    padding: 20px 22px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.dest-card-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 6px;
}

.dest-card-desc {
    font-size: 0.85rem;
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 12px;
    flex: 1;
}

.dest-card-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-top: auto;
}

.dest-card-rating {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.82rem;
    font-weight: 600;
    color: #f59e0b;
}
.dest-card-rating i { font-size: 0.78rem; }

.dest-card-price {
    font-size: 0.8rem;
    color: var(--text-tertiary);
}

/* Empty state */
.dest-empty {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-secondary);
}
.dest-empty i { font-size: 3rem; margin-bottom: 16px; display: block; color: var(--text-tertiary); }

/* Pagination */
.dest-pagination {
    display: flex;
    justify-content: center;
    gap: 4px;
}
.dest-pagination a,
.dest-pagination span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 10px;
    border-radius: var(--radius-md);
    font-size: 0.85rem;
    font-weight: 500;
    text-decoration: none;
    border: 1px solid var(--border);
    color: var(--text-secondary);
    background: var(--bg-card);
    transition: all var(--transition);
}
.dest-pagination a:hover {
    border-color: var(--primary-light);
    color: var(--primary-dark);
    background: var(--primary-50);
}
.dest-pagination .active span {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}
.dest-pagination .disabled span {
    opacity: 0.4;
    pointer-events: none;
}

@media (max-width: 1024px) {
    .dest-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .dest-grid { grid-template-columns: 1fr; }
    .dest-header h1 { font-size: 1.5rem; }
    .dest-filters { gap: 6px; }
    .dest-filter-btn { padding: 6px 14px; font-size: 0.8rem; }
}
</style>
@endsection

@section('content')
    @include('portal.publik._nav', ['active' => 'destinasi'])

    <div class="dest-page">
        <div class="container">
            {{-- Header --}}
            <div class="dest-header">
                <h1><i class="bi bi-compass" style="color: var(--primary);"></i> Destinasi Wisata Bojonegoro</h1>
                <p>Jelajahi destinasi wisata, kuliner, hotel, oleh-oleh, dan hiburan menarik di Kabupaten Bojonegoro.</p>
            </div>

            {{-- Category Filter --}}
            <div class="dest-filters">
                <a href="{{ url('/destinasi') }}" class="dest-filter-btn {{ empty($category) ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i> Semua
                </a>
                @foreach($categories as $key => $label)
                    <a href="{{ url('/destinasi?category=' . $key) }}"
                       class="dest-filter-btn {{ $category === $key ? 'active' : '' }}">
                        @switch($key)
                            @case('wisata') <i class="bi bi-tree"></i> @break
                            @case('kuliner') <i class="bi bi-cup-hot"></i> @break
                            @case('hotel') <i class="bi bi-building"></i> @break
                            @case('oleh-oleh') <i class="bi bi-bag"></i> @break
                            @case('hiburan') <i class="bi bi-music-note-beamed"></i> @break
                        @endswitch
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Grid --}}
            @if($destinations->count() > 0)
                <div class="dest-grid">
                    @foreach($destinations as $dest)
                        <a href="{{ route('publik.destinasi.show', $dest) }}" class="dest-card">
                            @if($dest->image_url)
                                <img src="{{ asset($dest->image_url) }}" alt="{{ $dest->name }}" class="dest-card-img" loading="lazy">
                            @else
                                <div class="dest-card-img-placeholder">
                                    <i class="bi bi-image-fill"></i>
                                </div>
                            @endif
                            <span class="dest-card-badge cat-{{ $dest->category }}">{{ $dest->category_label }}</span>
                            <div class="dest-card-body">
                                <h3 class="dest-card-title">{{ $dest->name }}</h3>
                                <p class="dest-card-desc">{{ str()->limit($dest->description, 100) }}</p>
                                <div class="dest-card-meta">
                                    <div class="dest-card-rating">
                                        <i class="bi bi-star-fill"></i>
                                        {{ number_format($dest->rating, 1) }}
                                        <span style="color: var(--text-tertiary); font-weight: 400;">({{ $dest->review_count }})</span>
                                    </div>
                                    @if($dest->price_range)
                                        <span class="dest-card-price">{{ $dest->price_range }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($destinations->hasPages())
                    <div class="dest-pagination">
                        {{ $destinations->links() }}
                    </div>
                @endif
            @else
                <div class="dest-empty">
                    <i class="bi bi-compass"></i>
                    <h3>Belum ada destinasi</h3>
                    <p>Destinasi wisata untuk kategori ini belum tersedia.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
