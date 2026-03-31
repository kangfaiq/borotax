@extends('layouts.portal-guest')

@section('title', $destination->name . ' - Destinasi Wisata Bojonegoro')

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
.detail-page { padding: 80px 0 60px; min-height: 100vh; }

/* Breadcrumb */
.detail-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: var(--text-tertiary);
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.detail-breadcrumb a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
}
.detail-breadcrumb a:hover { text-decoration: underline; }
.detail-breadcrumb .sep { color: var(--text-tertiary); }

/* Hero Image */
.detail-hero {
    width: 100%;
    height: 400px;
    border-radius: var(--radius-xl);
    overflow: hidden;
    margin-bottom: 32px;
    position: relative;
}
.detail-hero img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.detail-hero-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: var(--primary);
    background: linear-gradient(135deg, var(--primary-50) 0%, var(--primary-lighter) 100%);
}

/* Content Layout */
.detail-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 32px;
}

/* Main Info */
.detail-main {}

.detail-title-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.detail-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.3;
}
.detail-cat-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 14px;
    border-radius: var(--radius-full);
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
    margin-top: 6px;
}
.detail-cat-badge.cat-wisata { background: #16a34a; }
.detail-cat-badge.cat-kuliner { background: #ea580c; }
.detail-cat-badge.cat-hotel { background: #2563eb; }
.detail-cat-badge.cat-oleh-oleh { background: #7c3aed; }
.detail-cat-badge.cat-hiburan { background: #dc2626; }

.detail-rating {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 24px;
    font-size: 0.9rem;
}
.detail-rating-stars {
    display: flex;
    gap: 2px;
    color: #f59e0b;
    font-size: 1rem;
}
.detail-rating-text {
    font-weight: 600;
    color: var(--text-primary);
}
.detail-rating-count {
    color: var(--text-tertiary);
}

.detail-section-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.detail-section-title i {
    color: var(--primary);
    font-size: 1.1rem;
}

.detail-description {
    font-size: 0.92rem;
    color: var(--text-secondary);
    line-height: 1.8;
    margin-bottom: 32px;
    white-space: pre-line;
}

/* Facilities */
.detail-facilities {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 32px;
}
.detail-facility-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    background: var(--primary-50);
    color: var(--primary-dark);
    font-size: 0.82rem;
    font-weight: 600;
    border-radius: var(--radius-full);
    border: 1px solid var(--primary-lighter);
}
.detail-facility-chip i {
    font-size: 0.85rem;
}

/* Sidebar */
.detail-sidebar {}

.detail-info-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 24px;
    margin-bottom: 20px;
}
.detail-info-card h3 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.detail-info-card h3 i {
    color: var(--primary);
}

.detail-info-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
}
.detail-info-row:last-child { border-bottom: none; }

.detail-info-icon {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-md);
    background: var(--primary-50);
    color: var(--primary);
    font-size: 0.9rem;
}

.detail-info-text {
    flex: 1;
}
.detail-info-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-tertiary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}
.detail-info-value {
    font-size: 0.88rem;
    color: var(--text-primary);
    line-height: 1.5;
}
.detail-info-value a {
    color: var(--primary);
    text-decoration: none;
    word-break: break-all;
}
.detail-info-value a:hover { text-decoration: underline; }

/* Map Link Button */
.detail-map-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px 20px;
    background: var(--primary);
    color: #fff;
    font-size: 0.88rem;
    font-weight: 600;
    border-radius: var(--radius-lg);
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all var(--transition);
}
.detail-map-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Back Button */
.detail-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    color: var(--text-secondary);
    font-size: 0.88rem;
    font-weight: 500;
    text-decoration: none;
    transition: all var(--transition);
    margin-top: 32px;
}
.detail-back:hover {
    border-color: var(--primary-light);
    color: var(--primary-dark);
}

@media (max-width: 1024px) {
    .detail-content { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .detail-hero { height: 240px; border-radius: var(--radius-lg); }
    .detail-title { font-size: 1.35rem; }
    .detail-page { padding: 70px 0 40px; }
}
</style>
@endsection

@section('content')
    @include('portal.publik._nav', ['active' => 'destinasi'])

    <div class="detail-page">
        <div class="container">
            {{-- Breadcrumb --}}
            <nav class="detail-breadcrumb">
                <a href="{{ url('/') }}">Beranda</a>
                <span class="sep"><i class="bi bi-chevron-right"></i></span>
                <a href="{{ url('/destinasi') }}">Destinasi</a>
                <span class="sep"><i class="bi bi-chevron-right"></i></span>
                <span>{{ $destination->name }}</span>
            </nav>

            {{-- Hero Image --}}
            <div class="detail-hero">
                @if($destination->image_url)
                    <img src="{{ asset($destination->image_url) }}" alt="{{ $destination->name }}">
                @else
                    <div class="detail-hero-placeholder">
                        <i class="bi bi-image-fill"></i>
                    </div>
                @endif
            </div>

            {{-- Content --}}
            <div class="detail-content">
                {{-- Main --}}
                <div class="detail-main">
                    <div class="detail-title-row">
                        <h1 class="detail-title">{{ $destination->name }}</h1>
                        <span class="detail-cat-badge cat-{{ $destination->category }}">{{ $destination->category_label }}</span>
                    </div>

                    {{-- Rating --}}
                    <div class="detail-rating">
                        <div class="detail-rating-stars">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($destination->rating))
                                    <i class="bi bi-star-fill"></i>
                                @elseif($i - $destination->rating < 1 && $i - $destination->rating > 0)
                                    <i class="bi bi-star-half"></i>
                                @else
                                    <i class="bi bi-star"></i>
                                @endif
                            @endfor
                        </div>
                        <span class="detail-rating-text">{{ number_format($destination->rating, 1) }}</span>
                        <span class="detail-rating-count">({{ $destination->review_count }} ulasan)</span>
                    </div>

                    {{-- Description --}}
                    <h3 class="detail-section-title"><i class="bi bi-info-circle"></i> Deskripsi</h3>
                    <div class="detail-description">{{ $destination->description }}</div>

                    {{-- Facilities --}}
                    @if(!empty($destination->facilities))
                        <h3 class="detail-section-title"><i class="bi bi-check2-circle"></i> Fasilitas</h3>
                        <div class="detail-facilities">
                            @foreach($destination->facilities as $facility)
                                <span class="detail-facility-chip">
                                    <i class="bi bi-check-lg"></i> {{ $facility }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="detail-sidebar">
                    {{-- Location & Contact --}}
                    <div class="detail-info-card">
                        <h3><i class="bi bi-info-circle"></i> Informasi</h3>

                        {{-- Address --}}
                        <div class="detail-info-row">
                            <div class="detail-info-icon"><i class="bi bi-geo-alt"></i></div>
                            <div class="detail-info-text">
                                <div class="detail-info-label">Alamat</div>
                                <div class="detail-info-value">{{ $destination->address }}</div>
                            </div>
                        </div>

                        {{-- Price Range --}}
                        @if($destination->price_range)
                            <div class="detail-info-row">
                                <div class="detail-info-icon"><i class="bi bi-tag"></i></div>
                                <div class="detail-info-text">
                                    <div class="detail-info-label">Range Harga</div>
                                    <div class="detail-info-value">{{ $destination->price_range }}</div>
                                </div>
                            </div>
                        @endif

                        {{-- Phone --}}
                        @if($destination->phone)
                            <div class="detail-info-row">
                                <div class="detail-info-icon"><i class="bi bi-telephone"></i></div>
                                <div class="detail-info-text">
                                    <div class="detail-info-label">Telepon</div>
                                    <div class="detail-info-value">
                                        <a href="tel:{{ $destination->phone }}">{{ $destination->phone }}</a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Website --}}
                        @if($destination->website)
                            <div class="detail-info-row">
                                <div class="detail-info-icon"><i class="bi bi-globe"></i></div>
                                <div class="detail-info-text">
                                    <div class="detail-info-label">Website</div>
                                    <div class="detail-info-value">
                                        <a href="{{ $destination->website }}" target="_blank" rel="noopener noreferrer">
                                            {{ parse_url($destination->website, PHP_URL_HOST) ?? $destination->website }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Map --}}
                    @if($destination->latitude && $destination->longitude)
                        <div class="detail-info-card">
                            <h3><i class="bi bi-map"></i> Lokasi</h3>
                            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 16px;">
                                {{ number_format($destination->latitude, 6) }}, {{ number_format($destination->longitude, 6) }}
                            </p>
                            <a href="https://www.google.com/maps?q={{ $destination->latitude }},{{ $destination->longitude }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="detail-map-btn">
                                <i class="bi bi-geo-alt-fill"></i> Buka di Google Maps
                            </a>
                        </div>
                    @endif

                    {{-- Back Button --}}
                    <a href="{{ url('/destinasi') }}" class="detail-back">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Destinasi
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
