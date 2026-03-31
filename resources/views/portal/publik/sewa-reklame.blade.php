@extends('layouts.portal-guest')

@section('title', 'Sewa Reklame - Borotax')

@section('navbar-class', 'navbar-light')

@section('nav-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/sewa-reklame') }}" style="color: var(--primary-dark); font-weight: 600;">Sewa Reklame</a>
    <a href="{{ url('/login') }}" class="btn btn-primary btn-sm navbar-cta">Login</a>
@endsection

@section('nav-mobile-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/sewa-reklame') }}">Sewa Reklame</a>
    <a href="{{ url('/login') }}">Login Wajib Pajak</a>
@endsection

@section('styles')
<style>
.sewa-page { padding: 100px 0 60px; min-height: 100vh; }
.sewa-header { text-align: center; margin-bottom: 48px; }
.sewa-header h1 { font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; }
.sewa-header p { color: var(--text-secondary); font-size: 0.95rem; max-width: 600px; margin: 0 auto; }

/* Info Card */
.sewa-info {
    max-width: 900px; margin: 0 auto 32px;
    background: linear-gradient(135deg, #0E7490 0%, #0891B2 100%);
    border-radius: var(--radius-xl); padding: 28px 32px; color: white;
}
.sewa-info h3 { font-size: 1rem; font-weight: 700; margin-bottom: 12px; }
.sewa-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.sewa-info-item { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; opacity: 0.9; }
.sewa-info-item i { font-size: 1rem; opacity: 0.7; }
.sewa-info-actions { display: flex; gap: 10px; margin-top: 16px; }
.sewa-info-actions a {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: var(--radius-full); font-size: 0.82rem; font-weight: 600;
    background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.3);
    text-decoration: none; transition: all var(--transition);
}
.sewa-info-actions a:hover { background: rgba(255,255,255,0.25); }

/* Tabs */
.sewa-tabs { max-width: 900px; margin: 0 auto 24px; display: flex; gap: 4px; }
.sewa-tab {
    padding: 10px 24px; border-radius: var(--radius-full);
    font-size: 0.88rem; font-weight: 600; cursor: pointer;
    border: 1px solid var(--border); background: var(--bg-card);
    color: var(--text-secondary); transition: all var(--transition);
}
.sewa-tab:hover { border-color: var(--primary); color: var(--primary); }
.sewa-tab.active { background: var(--primary); color: white; border-color: var(--primary); }

/* Asset Cards */
.sewa-grid { max-width: 900px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

.asset-card {
    background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg);
    padding: 20px; cursor: pointer; transition: all var(--transition);
}
.asset-card:hover { border-color: var(--primary-light); box-shadow: var(--shadow-md); transform: translateY(-2px); }

.asset-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.asset-id { font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: var(--radius-full); background: var(--primary-50); color: var(--primary-dark); }
.asset-status { font-size: 0.72rem; font-weight: 600; padding: 3px 10px; border-radius: var(--radius-full); background: #DCFCE7; color: #16A34A; }

.asset-lokasi { font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
.asset-keterangan { font-size: 0.8rem; color: var(--text-tertiary); margin-bottom: 10px; }

.asset-specs { display: flex; gap: 16px; margin-bottom: 12px; }
.asset-spec { font-size: 0.78rem; color: var(--text-secondary); display: flex; align-items: center; gap: 4px; }
.asset-spec i { font-size: 0.85rem; color: var(--primary); }

.asset-price { font-size: 0.75rem; color: var(--text-tertiary); }
.asset-price strong { font-size: 0.95rem; color: var(--text-primary); font-weight: 800; }

.asset-tenant {
    margin-top: 10px; padding: 10px 12px; border-radius: var(--radius-md);
    background: #EFF6FF; border: 1px solid #BFDBFE; font-size: 0.76rem;
}
.asset-tenant .at-label { color: #1E40AF; font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 4px; }
.asset-tenant .at-materi { color: #1E3A5F; font-weight: 600; margin-bottom: 2px; }
.asset-tenant .at-durasi { color: #64748B; }

/* Detail Modal */
.detail-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4);
    z-index: 2000; align-items: flex-end; justify-content: center;
}
.detail-overlay.active { display: flex; }
.detail-modal {
    background: var(--bg-card); border-radius: var(--radius-xl) var(--radius-xl) 0 0;
    width: 100%; max-width: 600px; max-height: 85vh; overflow-y: auto;
    padding: 28px 32px; animation: slideUp 0.3s ease-out;
}
@keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
.detail-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.detail-header h3 { font-size: 1.1rem; font-weight: 800; color: var(--text-primary); }
.detail-close { background: none; border: none; cursor: pointer; font-size: 1.3rem; color: var(--text-tertiary); padding: 4px; }

.detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-light); }
.detail-row .dl { font-size: 0.82rem; color: var(--text-secondary); }
.detail-row .dv { font-size: 0.85rem; font-weight: 600; color: var(--text-primary); }

.detail-pricing { margin-top: 16px; }
.detail-pricing h4 { font-size: 0.85rem; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
.detail-price-row { display: flex; justify-content: space-between; padding: 8px 14px; background: var(--primary-50); border-radius: var(--radius-sm); margin-bottom: 4px; }
.detail-price-row .label { font-size: 0.82rem; color: var(--text-secondary); }
.detail-price-row .value { font-size: 0.88rem; font-weight: 700; color: var(--primary-dark); }

.detail-actions { display: flex; gap: 10px; margin-top: 20px; }
.detail-actions a {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 12px; border-radius: var(--radius-full); font-size: 0.88rem; font-weight: 600;
    text-decoration: none; transition: all var(--transition);
}
.detail-map { background: var(--primary); color: white; }
.detail-map:hover { background: var(--primary-dark); }
.detail-call { background: var(--bg-surface); color: var(--text-primary); border: 1px solid var(--border); }
.detail-call:hover { background: var(--primary-50); border-color: var(--primary); }

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
    .sewa-grid { grid-template-columns: 1fr; }
    .sewa-info-grid { grid-template-columns: 1fr; }
    .sewa-info-actions { flex-direction: column; }
    .detail-modal { padding: 20px; }
    .detail-actions { flex-direction: column; }
}
</style>
@endsection

@section('content')
<section class="sewa-page">
    <div class="container">
        <div class="sewa-header">
            <span class="section-badge"><i class="bi bi-signpost-2"></i> LAYANAN PUBLIK</span>
            <h1>Sewa Reklame</h1>
            <p>Informasi lokasi dan tarif sewa ruang reklame milik Pemerintah Kabupaten Bojonegoro</p>
        </div>

        @include('portal.publik._nav', ['active' => 'sewa-reklame'])

        {{-- Info Card --}}
        <div class="sewa-info">
            <h3><i class="bi bi-info-circle"></i> Informasi Penyewaan</h3>
            <div class="sewa-info-grid">
                <div class="sewa-info-item"><i class="bi bi-building"></i> Dikelola oleh BAPENDA Kab. Bojonegoro</div>
                <div class="sewa-info-item"><i class="bi bi-file-earmark-text"></i> Dasar Hukum: Perbup No. 70/2025</div>
                <div class="sewa-info-item"><i class="bi bi-calendar3"></i> Tahun Anggaran 2026</div>
                <div class="sewa-info-item"><i class="bi bi-geo-alt"></i> Jl. P. Mas Tumapel No. 1 Bojonegoro</div>
            </div>
            <div class="sewa-info-actions">
                <a href="{{ route('sewa-reklame.cek') }}"><i class="bi bi-search"></i> Cek Status Permohonan</a>
                <a href="tel:0353881826"><i class="bi bi-telephone"></i> (0353) 881826</a>
                <a href="mailto:bapenda@bojonegorokab.go.id"><i class="bi bi-envelope"></i> bapenda@bojonegorokab.go.id</a>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="sewa-tabs">
            @php $jenisTypes = $asetReklame->pluck('jenis')->unique()->values(); @endphp
            @foreach($jenisTypes as $i => $jenis)
                <div class="sewa-tab {{ $i === 0 ? 'active' : '' }}" onclick="switchTab('{{ $jenis }}')">
                    {{ $jenis === 'neon_box' ? 'Neon Box' : 'Billboard' }}
                </div>
            @endforeach
        </div>

        {{-- Grid --}}
        <div class="sewa-grid" id="assetGrid"></div>
    </div>
</section>

{{-- Detail Modal --}}
<div class="detail-overlay" id="detailOverlay" onclick="closeDetail(event)">
    <div class="detail-modal" onclick="event.stopPropagation()">
        <div class="detail-header">
            <h3 id="detailTitle">Detail Aset</h3>
            <button class="detail-close" onclick="closeDetail()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div id="detailContent"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ========================================
// Sewa Reklame — Dynamic Asset Data
// ========================================

const ASSETS = @json($asetReklame->groupBy('jenis'));

let currentTab = Object.keys(ASSETS)[0] || 'neon_box';

function formatCurrency(amount) {
    return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
}

function getStatusBadge(status, label) {
    const colors = {
        tersedia: { bg: '#DCFCE7', color: '#16A34A', icon: 'check-circle' },
        disewa: { bg: '#DBEAFE', color: '#2563EB', icon: 'lock' },
        maintenance: { bg: '#FEF3C7', color: '#D97706', icon: 'wrench' },
        tidak_aktif: { bg: '#FEE2E2', color: '#DC2626', icon: 'x-circle' },
        dipinjam_opd: { bg: '#E0F2F1', color: '#00796B', icon: 'building' },
    };
    const c = colors[status] || colors.tidak_aktif;
    return `<span class="asset-status" style="background:${c.bg};color:${c.color}"><i class="bi bi-${c.icon}"></i> ${label}</span>`;
}

function switchTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.sewa-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    renderAssets();
}

function renderAssets() {
    const grid = document.getElementById('assetGrid');
    const assets = ASSETS[currentTab] || [];
    const isNeonBox = currentTab === 'neon_box';

    if (assets.length === 0) {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-tertiary);">Belum ada data aset untuk kategori ini.</div>';
        return;
    }

    grid.innerHTML = assets.map((a, idx) => `
        <div class="asset-card" onclick="showDetail('${currentTab}', ${idx})">
            <div class="asset-card-header">
                <span class="asset-id">${a.kode_aset}</span>
                ${getStatusBadge(a.status_ketersediaan, a.status_label)}
            </div>
            <div class="asset-lokasi">${a.lokasi || a.nama}</div>
            ${a.kawasan ? `<div class="asset-keterangan">${a.kawasan}</div>` : ''}
            ${a.keterangan ? `<div class="asset-keterangan" style="margin-top:-4px;"><i class="bi bi-info-circle" style="font-size:0.75rem;"></i> ${a.keterangan}</div>` : ''}
            <div class="asset-specs">
                <span class="asset-spec"><i class="bi bi-arrows-fullscreen"></i> ${a.ukuran_formatted}</span>
                <span class="asset-spec"><i class="bi bi-layers"></i> ${a.jumlah_muka} muka</span>
            </div>
            <div class="asset-price">Mulai <strong>${a.harga_sewa_per_bulan ? formatCurrency(a.harga_sewa_per_bulan) + ' / bulan' : (a.harga_sewa_per_tahun ? formatCurrency(a.harga_sewa_per_tahun) + ' / tahun' : '-')}</strong></div>
            ${a.penyewa_aktif ? `
            <div class="asset-tenant">
                <div class="at-label"><i class="bi bi-megaphone"></i> Materi Terpasang</div>
                <div class="at-materi">${a.penyewa_aktif.materi || '-'}</div>
                <div class="at-durasi"><i class="bi bi-calendar-range"></i> ${a.penyewa_aktif.mulai} s/d ${a.penyewa_aktif.sampai}</div>
            </div>` : ''}
        </div>
    `).join('');
}

function showDetail(tab, idx) {
    const asset = ASSETS[tab][idx];
    const luas = asset.luas_m2 || (asset.panjang * asset.lebar * asset.jumlah_muka);

    document.getElementById('detailTitle').textContent = asset.lokasi || asset.nama;

    let html = '';
    if (asset.kawasan) html += makeRow('Kawasan', asset.kawasan);
    if (asset.keterangan) html += makeRow('Keterangan', asset.keterangan);
    html += makeRow('Kode Aset', asset.kode_aset);
    html += makeRow('Jenis', asset.jenis === 'neon_box' ? 'Neon Box' : 'Billboard');
    html += makeRow('Dimensi', asset.ukuran_formatted);
    html += makeRow('Jumlah Muka', asset.jumlah_muka);
    html += makeRow('Luas', parseFloat(luas).toFixed(2) + ' m²');
    html += makeRow('Status', asset.status_label);

    if (asset.penyewa_aktif) {
        html += `<div style="margin-top:16px;padding:14px 16px;background:#EFF6FF;border:1px solid #BFDBFE;border-radius:var(--radius-md);">`;
        html += `<div style="font-size:0.82rem;font-weight:700;color:#1E40AF;margin-bottom:8px;display:flex;align-items:center;gap:6px;"><i class="bi bi-megaphone-fill"></i> Informasi Materi Terpasang</div>`;
        html += `<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #DBEAFE;"><span style="font-size:0.8rem;color:#64748B;">Materi</span><span style="font-size:0.82rem;font-weight:600;color:#1E3A5F;">${asset.penyewa_aktif.materi || '-'}</span></div>`;
        if (asset.penyewa_aktif.jenis_reklame) {
            html += `<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #DBEAFE;"><span style="font-size:0.8rem;color:#64748B;">Jenis Reklame</span><span style="font-size:0.82rem;font-weight:600;color:#1E3A5F;">${asset.penyewa_aktif.jenis_reklame}</span></div>`;
        }
        html += `<div style="display:flex;justify-content:space-between;padding:6px 0;"><span style="font-size:0.8rem;color:#64748B;">Masa Tayang</span><span style="font-size:0.82rem;font-weight:600;color:#1E3A5F;">${asset.penyewa_aktif.mulai} s/d ${asset.penyewa_aktif.sampai}</span></div>`;
        html += `</div>`;
    }

    html += `<div class="detail-pricing"><h4><i class="bi bi-tag"></i> Tarif Sewa</h4>`;
    if (asset.harga_sewa_per_tahun) html += `<div class="detail-price-row"><span class="label">Per Tahun</span><span class="value">${formatCurrency(asset.harga_sewa_per_tahun)}</span></div>`;
    if (asset.harga_sewa_per_bulan) html += `<div class="detail-price-row"><span class="label">Per Bulan</span><span class="value">${formatCurrency(asset.harga_sewa_per_bulan)}</span></div>`;
    html += `</div>`;

    html += `<div class="detail-actions">`;
    if (asset.lat && asset.lng) {
        html += `<a href="https://www.google.com/maps?q=${asset.lat},${asset.lng}" target="_blank" rel="noopener noreferrer" class="detail-map"><i class="bi bi-map"></i> Lihat di Peta</a>`;
    }
    if (asset.status_ketersediaan === 'tersedia') {
        html += `<a href="{{ url('/sewa-reklame/ajukan') }}/${asset.id}" class="detail-map" style="background:#16A34A;"><i class="bi bi-pencil-square"></i> Ajukan Sewa</a>`;
    }
    html += `<a href="tel:0353881826" class="detail-call"><i class="bi bi-telephone"></i> Hubungi BAPENDA</a>`;
    html += `</div>`;

    document.getElementById('detailContent').innerHTML = html;
    document.getElementById('detailOverlay').classList.add('active');
}

function makeRow(label, value) {
    return `<div class="detail-row"><span class="dl">${label}</span><span class="dv">${value}</span></div>`;
}

function closeDetail(e) {
    if (e && e.target !== document.getElementById('detailOverlay')) return;
    document.getElementById('detailOverlay').classList.remove('active');
}

document.addEventListener('DOMContentLoaded', renderAssets);
</script>
@endsection
