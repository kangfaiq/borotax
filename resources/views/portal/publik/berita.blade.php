@extends('layouts.portal-guest')

@section('title', 'Berita - Borotax')

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
.news-page { padding: 100px 0 60px; min-height: 100vh; }

.news-header { text-align: center; margin-bottom: 40px; }
.news-header h1 { font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; }
.news-header p { color: var(--text-secondary); font-size: 0.95rem; max-width: 600px; margin: 0 auto; }

/* Category Filter Tabs */
.news-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
    margin-bottom: 40px;
}
.news-filter-btn {
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
.news-filter-btn:hover {
    border-color: var(--primary-light);
    color: var(--primary-dark);
    background: var(--primary-50);
}
.news-filter-btn.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}

/* Grid */
.news-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}

/* Card */
.news-card-item {
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
.news-card-item:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-xl);
    border-color: transparent;
}

.news-card-item-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    background: linear-gradient(135deg, var(--primary-50) 0%, var(--primary-lighter) 100%);
}
.news-card-item-img-placeholder {
    width: 100%;
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: var(--primary);
    background: linear-gradient(135deg, var(--primary-50) 0%, var(--primary-lighter) 100%);
}

.news-cat-badge {
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
.news-cat-badge.cat-pengumuman { background: #dc2626; }
.news-cat-badge.cat-pajak { background: #16a34a; }
.news-cat-badge.cat-event { background: #ea580c; }
.news-cat-badge.cat-edukasi { background: #2563eb; }
.news-cat-badge.cat-lainnya { background: #6b7280; }

.news-card-item-body {
    padding: 20px 22px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.news-card-item-date {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.78rem;
    color: var(--text-tertiary);
    margin-bottom: 8px;
}

.news-card-item-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
    line-height: 1.4;
}

.news-card-item-excerpt {
    font-size: 0.85rem;
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 12px;
    flex: 1;
}

.news-card-item-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-top: auto;
    font-size: 0.8rem;
    color: var(--text-tertiary);
}
.news-card-item-footer i { font-size: 0.78rem; }

/* Empty state */
.news-empty {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-secondary);
}
.news-empty i { font-size: 3rem; margin-bottom: 16px; display: block; color: var(--text-tertiary); }

/* Pagination */
.news-pagination {
    display: flex;
    justify-content: center;
    gap: 4px;
}
.news-pagination a,
.news-pagination span {
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
.news-pagination a:hover {
    border-color: var(--primary-light);
    color: var(--primary-dark);
    background: var(--primary-50);
}
.news-pagination .active span {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}
.news-pagination .disabled span {
    opacity: 0.4;
    pointer-events: none;
}

@media (max-width: 1024px) {
    .news-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .news-grid { grid-template-columns: 1fr; }
    .news-header h1 { font-size: 1.5rem; }
    .news-filters { gap: 6px; }
    .news-filter-btn { padding: 6px 14px; font-size: 0.8rem; }
}
</style>
@endsection

@section('content')
    @include('portal.publik._nav', ['active' => 'berita'])

    <div class="news-page">
        <div class="container">
            {{-- Header --}}
            <div class="news-header">
                <h1><i class="bi bi-newspaper" style="color: var(--primary);"></i> Berita Terbaru</h1>
                <p>Informasi dan berita terkini seputar perpajakan daerah Kabupaten Bojonegoro.</p>
            </div>

            {{-- Category Filter --}}
            <div class="news-filters">
                <a href="{{ url('/berita') }}" class="news-filter-btn {{ empty($category) ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i> Semua
                </a>
                @foreach($categories as $key => $label)
                    <a href="{{ url('/berita?category=' . $key) }}"
                       class="news-filter-btn {{ $category === $key ? 'active' : '' }}">
                        @switch($key)
                            @case('pengumuman') <i class="bi bi-megaphone"></i> @break
                            @case('pajak') <i class="bi bi-receipt"></i> @break
                            @case('event') <i class="bi bi-calendar-event"></i> @break
                            @case('edukasi') <i class="bi bi-mortarboard"></i> @break
                            @case('lainnya') <i class="bi bi-tag"></i> @break
                        @endswitch
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Grid --}}
            @if($news->count() > 0)
                <div class="news-grid">
                    @foreach($news as $item)
                        <a href="{{ route('publik.berita.show', $item) }}" class="news-card-item">
                            @if($item->image_url)
                                <img src="{{ asset($item->image_url) }}" alt="{{ $item->title }}" class="news-card-item-img" loading="lazy">
                            @else
                                <div class="news-card-item-img-placeholder">
                                    <i class="bi bi-newspaper"></i>
                                </div>
                            @endif
                            <span class="news-cat-badge cat-{{ $item->category }}">
                                {{ $categories[$item->category] ?? $item->category }}
                            </span>
                            <div class="news-card-item-body">
                                <div class="news-card-item-date">
                                    <i class="bi bi-calendar3"></i>
                                    {{ \Carbon\Carbon::parse($item->published_at ?? $item->created_at)->format('d M Y') }}
                                </div>
                                <h3 class="news-card-item-title">{{ str()->limit($item->title, 80) }}</h3>
                                <p class="news-card-item-excerpt">{{ str()->limit(strip_tags($item->content), 120) }}</p>
                                <div class="news-card-item-footer">
                                    <span><i class="bi bi-person"></i> {{ $item->author ?? 'Admin' }}</span>
                                    <span><i class="bi bi-eye"></i> {{ $item->view_count }} views</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($news->hasPages())
                    <div class="news-pagination">
                        {{ $news->links() }}
                    </div>
                @endif
            @else
                <div class="news-empty">
                    <i class="bi bi-newspaper"></i>
                    <h3>Belum ada berita</h3>
                    <p>Berita untuk kategori ini belum tersedia.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
