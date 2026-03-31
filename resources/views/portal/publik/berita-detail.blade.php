@extends('layouts.portal-guest')

@section('title', $news->title . ' - Berita Borotax')

@section('navbar-class', 'navbar-light')

@section('nav-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/berita') }}" style="color: var(--primary-dark); font-weight: 600;">Berita</a>
    <a href="{{ url('/login') }}" class="btn btn-primary btn-sm navbar-cta">Login</a>
@endsection

@section('nav-mobile-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/berita') }}">Berita</a>
    <a href="{{ url('/login') }}">Login Wajib Pajak</a>
@endsection

@section('styles')
<style>
.news-detail-page { padding: 80px 0 60px; min-height: 100vh; }

/* Breadcrumb */
.news-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: var(--text-tertiary);
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.news-breadcrumb a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
}
.news-breadcrumb a:hover { text-decoration: underline; }
.news-breadcrumb .sep { color: var(--text-tertiary); }

/* Hero Image */
.news-detail-hero {
    width: 100%;
    height: 400px;
    border-radius: var(--radius-xl);
    overflow: hidden;
    margin-bottom: 32px;
    position: relative;
}
.news-detail-hero img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.news-detail-hero-placeholder {
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
.news-detail-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 32px;
}

/* Main Content */
.news-detail-main {}

.news-detail-category {
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
    margin-bottom: 12px;
}
.news-detail-category.cat-pengumuman { background: #dc2626; }
.news-detail-category.cat-pajak { background: #16a34a; }
.news-detail-category.cat-event { background: #ea580c; }
.news-detail-category.cat-edukasi { background: #2563eb; }
.news-detail-category.cat-lainnya { background: #6b7280; }

.news-detail-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.3;
    margin-bottom: 16px;
}

.news-detail-meta {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 32px;
    font-size: 0.85rem;
    color: var(--text-tertiary);
    flex-wrap: wrap;
}
.news-detail-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
}
.news-detail-meta-item i { font-size: 0.9rem; }

.news-detail-content {
    font-size: 0.95rem;
    color: var(--text-secondary);
    line-height: 1.9;
    margin-bottom: 32px;
}
.news-detail-content p {
    margin-bottom: 16px;
}
.news-detail-content img {
    max-width: 100%;
    height: auto;
    border-radius: var(--radius-lg);
    margin: 16px 0;
}

/* Source Link */
.news-detail-source {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px 20px;
    background: var(--primary-50);
    border: 1px solid var(--primary-lighter);
    border-radius: var(--radius-lg);
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-bottom: 32px;
}
.news-detail-source a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    word-break: break-all;
}
.news-detail-source a:hover { text-decoration: underline; }

/* Sidebar */
.news-detail-sidebar {}

.news-sidebar-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 24px;
    margin-bottom: 20px;
}
.news-sidebar-card h3 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.news-sidebar-card h3 i {
    color: var(--primary);
}

/* Related News List */
.news-related-item {
    display: flex;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
    text-decoration: none;
    color: inherit;
    transition: all var(--transition);
}
.news-related-item:last-child { border-bottom: none; }
.news-related-item:hover { opacity: 0.8; }

.news-related-img {
    width: 80px;
    height: 55px;
    border-radius: var(--radius-md);
    object-fit: cover;
    flex-shrink: 0;
    background: var(--primary-50);
}
.news-related-img-placeholder {
    width: 80px;
    height: 55px;
    border-radius: var(--radius-md);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: var(--primary);
    background: linear-gradient(135deg, var(--primary-50) 0%, var(--primary-lighter) 100%);
}

.news-related-text {
    flex: 1;
    min-width: 0;
}
.news-related-title {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.4;
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.news-related-date {
    font-size: 0.72rem;
    color: var(--text-tertiary);
}

/* Back Button */
.news-detail-back {
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
.news-detail-back:hover {
    border-color: var(--primary-light);
    color: var(--primary-dark);
}

@media (max-width: 1024px) {
    .news-detail-layout { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .news-detail-hero { height: 240px; border-radius: var(--radius-lg); }
    .news-detail-title { font-size: 1.35rem; }
    .news-detail-page { padding: 70px 0 40px; }
}
</style>
@endsection

@section('content')
    @include('portal.publik._nav', ['active' => 'berita'])

    <div class="news-detail-page">
        <div class="container">
            {{-- Breadcrumb --}}
            <nav class="news-breadcrumb">
                <a href="{{ url('/') }}">Beranda</a>
                <span class="sep"><i class="bi bi-chevron-right"></i></span>
                <a href="{{ url('/berita') }}">Berita</a>
                <span class="sep"><i class="bi bi-chevron-right"></i></span>
                <span>{{ str()->limit($news->title, 50) }}</span>
            </nav>

            {{-- Hero Image --}}
            <div class="news-detail-hero">
                @if($news->image_url)
                    <img src="{{ asset($news->image_url) }}" alt="{{ $news->title }}">
                @else
                    <div class="news-detail-hero-placeholder">
                        <i class="bi bi-newspaper"></i>
                    </div>
                @endif
            </div>

            {{-- Content Layout --}}
            <div class="news-detail-layout">
                {{-- Main Content --}}
                <div class="news-detail-main">
                    <span class="news-detail-category cat-{{ $news->category }}">
                        {{ match($news->category) {
                            'pengumuman' => 'Pengumuman',
                            'pajak' => 'Informasi Pajak',
                            'event' => 'Event',
                            'edukasi' => 'Edukasi',
                            default => 'Lainnya',
                        } }}
                    </span>

                    <h1 class="news-detail-title">{{ $news->title }}</h1>

                    <div class="news-detail-meta">
                        <span class="news-detail-meta-item">
                            <i class="bi bi-calendar3"></i>
                            {{ \Carbon\Carbon::parse($news->published_at ?? $news->created_at)->format('d M Y, H:i') }}
                        </span>
                        <span class="news-detail-meta-item">
                            <i class="bi bi-person"></i>
                            {{ $news->author ?? 'Admin' }}
                        </span>
                        <span class="news-detail-meta-item">
                            <i class="bi bi-eye"></i>
                            {{ $news->view_count }} views
                        </span>
                    </div>

                    <div class="news-detail-content">
                        {!! $news->content !!}
                    </div>

                    @if($news->source_url)
                        <div class="news-detail-source">
                            <i class="bi bi-link-45deg"></i>
                            <span>Sumber: <a href="{{ $news->source_url }}" target="_blank" rel="noopener noreferrer">{{ parse_url($news->source_url, PHP_URL_HOST) }}</a></span>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="news-detail-sidebar">
                    {{-- Related News --}}
                    @if($relatedNews->count() > 0)
                        <div class="news-sidebar-card">
                            <h3><i class="bi bi-newspaper"></i> Berita Lainnya</h3>
                            @foreach($relatedNews as $related)
                                <a href="{{ route('publik.berita.show', $related) }}" class="news-related-item">
                                    @if($related->image_url)
                                        <img src="{{ asset($related->image_url) }}" alt="{{ $related->title }}" class="news-related-img" loading="lazy">
                                    @else
                                        <div class="news-related-img-placeholder">
                                            <i class="bi bi-newspaper"></i>
                                        </div>
                                    @endif
                                    <div class="news-related-text">
                                        <div class="news-related-title">{{ $related->title }}</div>
                                        <div class="news-related-date">
                                            <i class="bi bi-calendar3"></i>
                                            {{ \Carbon\Carbon::parse($related->published_at ?? $related->created_at)->format('d M Y') }}
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Back Button --}}
                    <a href="{{ url('/berita') }}" class="news-detail-back">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Berita
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
