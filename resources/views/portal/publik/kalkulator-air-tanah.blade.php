@extends('layouts.portal-guest')

@section('title', 'Kalkulator Pajak Air Tanah - Borotax')

@section('navbar-class', 'navbar-light')

@section('nav-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/kalkulator-air-tanah') }}" style="color: var(--primary-dark); font-weight: 600;">Kalkulator Air Tanah</a>
    <a href="{{ url('/login') }}" class="btn btn-primary btn-sm navbar-cta">Login</a>
@endsection

@section('nav-mobile-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/kalkulator-air-tanah') }}">Kalkulator Air Tanah</a>
    <a href="{{ url('/login') }}">Login Wajib Pajak</a>
@endsection

@section('styles')
<style>
.calc-page { padding: 100px 0 60px; min-height: 100vh; }
.calc-header { text-align: center; margin-bottom: 48px; }
.calc-header h1 { font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; }
.calc-header p { color: var(--text-secondary); font-size: 0.95rem; max-width: 600px; margin: 0 auto; }

.calc-box {
    background: var(--bg-card);
    border-radius: var(--radius-xl);
    border: 1px solid var(--border);
    padding: 36px;
    max-width: 700px;
    margin: 0 auto;
    box-shadow: var(--shadow-md);
}

.calc-form-group { margin-bottom: 20px; }
.calc-form-group label {
    display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-primary);
    margin-bottom: 6px;
}
.calc-form-group select,
.calc-form-group input {
    width: 100%; padding: 12px 16px; border: 1px solid var(--border);
    border-radius: var(--radius-md); font-family: inherit; font-size: 0.9rem;
    color: var(--text-primary); background: var(--bg-surface-variant);
    transition: border-color var(--transition); outline: none;
}
.calc-form-group select:focus,
.calc-form-group input:focus { border-color: var(--primary); }

.calc-form-group .help-text {
    font-size: 0.78rem; color: var(--text-tertiary); margin-top: 4px; line-height: 1.5;
}

.calc-btn {
    width: 100%; padding: 14px; border: none; border-radius: var(--radius-full);
    background: var(--primary); color: white; font-weight: 700; font-size: 0.95rem;
    cursor: pointer; transition: all var(--transition); font-family: inherit; margin-top: 8px;
}
.calc-btn:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: var(--shadow-primary); }

.calc-result {
    margin-top: 28px; border-radius: var(--radius-lg);
    background: linear-gradient(135deg, #1E293B 0%, #334155 100%);
    color: white; display: none;
}
.calc-result.visible { display: block; animation: fadeInUp 0.4s ease-out; }

.calc-result-header {
    padding: 20px 24px 12px;
}
.calc-result-header h3 { font-size: 1rem; font-weight: 700; margin-bottom: 0; opacity: 0.9; }

.calc-result-table {
    width: 100%; border-collapse: collapse; margin: 0;
}
.calc-result-table th {
    padding: 10px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;
    color: rgba(255,255,255,0.6); text-align: left; border-bottom: 1px solid rgba(255,255,255,0.1);
    font-weight: 600;
}
.calc-result-table td {
    padding: 10px 16px; font-size: 0.85rem; color: rgba(255,255,255,0.9);
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.calc-result-table td.num { text-align: right; font-variant-numeric: tabular-nums; }

.calc-result-summary {
    padding: 16px 24px 20px;
}
.calc-result-row {
    display: flex; justify-content: space-between; align-items: center; padding: 6px 0;
}
.calc-result-row .label { font-size: 0.85rem; opacity: 0.8; }
.calc-result-row .value { font-size: 0.9rem; font-weight: 700; }
.calc-result-total {
    margin-top: 8px; padding-top: 12px; border-top: 2px solid rgba(255,255,255,0.2);
    display: flex; justify-content: space-between; align-items: center;
}
.calc-result-total .label { font-size: 0.95rem; font-weight: 700; }
.calc-result-total .value { font-size: 1.2rem; font-weight: 800; color: #FCD34D; }

.info-toggle {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 0.78rem; color: var(--primary); cursor: pointer; font-weight: 600;
    margin-top: 4px;
}
.info-toggle:hover { text-decoration: underline; }

.kelompok-info {
    display: none; margin-top: 8px; padding: 12px 16px; background: var(--primary-50);
    border-radius: var(--radius-md); font-size: 0.8rem; color: var(--text-secondary);
    line-height: 1.7;
}
.kelompok-info.visible { display: block; }

.calc-disclaimer {
    margin-top: 16px; padding: 12px 16px; background: rgba(249,168,38,0.1);
    border-radius: var(--radius-md); border-left: 3px solid var(--accent);
    font-size: 0.78rem; color: var(--text-secondary); line-height: 1.6;
}

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
    .calc-box { padding: 24px 20px; }
    .calc-result-table th, .calc-result-table td { padding: 8px 12px; font-size: 0.78rem; }
}
</style>
@endsection

@section('content')
<section class="calc-page">
    <div class="container">
        <div class="calc-header">
            <span class="section-badge"><i class="bi bi-droplet"></i> LAYANAN PUBLIK</span>
            <h1>Kalkulator Pajak Air Tanah</h1>
            <p>Estimasi pajak air tanah berdasarkan rentang tarif progresif dan kriteria SDA.</p>
        </div>

        @include('portal.publik._nav', ['active' => 'kalkulator-air-tanah'])

        <div class="calc-box">
            <div class="calc-form-group">
                <label>Skenario Penetapan Aturan NPA</label>
                <select id="scenario">
                    <option value="lama">Aturan Lama (≤ Juni 2026) — Zona 3</option>
                    <option value="baru_baik_ada">Aturan Baru (≥ Juli 2026) — Air Kualitas Baik, Ada Sumber Alternatif</option>
                    <option value="baru_baik_tidak">Aturan Baru (≥ Juli 2026) — Air Kualitas Baik, Tidak Ada Sumber Alternatif</option>
                    <option value="baru_tidak_ada">Aturan Baru (≥ Juli 2026) — Air Kualitas Tidak Baik, Ada Sumber Alternatif</option>
                    <option value="baru_tidak_tidak">Aturan Baru (≥ Juli 2026) — Air Kualitas Tidak Baik, Tidak Ada Sumber Alternatif</option>
                </select>
            </div>

            <div class="calc-form-group">
                <label>Kelompok Pemakaian</label>
                <select id="kelompok">
                    <option value="0">Kelompok 1 — Badan Usaha Air Sebagai Produk</option>
                    <option value="1">Kelompok 2 — Pengguna Besar Non-Produk Air</option>
                    <option value="2">Kelompok 3 — Pengguna Menengah Non-Produk Air</option>
                    <option value="3">Kelompok 4 — Pengguna Kecil Non-Produk Air</option>
                    <option value="4">Kelompok 5 — Pengguna Kebutuhan Dasar</option>
                </select>
                <span class="info-toggle" onclick="toggleKelompokInfo()">
                    <i class="bi bi-info-circle"></i> Lihat deskripsi kelompok
                </span>
                <div class="kelompok-info" id="kelompokInfo">
                    <strong>Kelompok 1:</strong> Badan usaha pengambilan air tanah yang air merupakan produk utama (pemasok air bersih, air minum kemasan, pabrik es, dll) — penggunaan air >95% sebagai bahan baku.<br>
                    <strong>Kelompok 2:</strong> Pengguna besar non-produk air (tekstil, makanan olahan, hotel bintang 3-5, farmasi) — pemakaian >2.000 m³/bulan.<br>
                    <strong>Kelompok 3:</strong> Pengguna menengah non-produk air (hotel bintang 1-2, apartemen, budidaya ikan) — pemakaian 1.500–2.000 m³/bulan.<br>
                    <strong>Kelompok 4:</strong> Pengguna kecil non-produk air (penginapan, hiburan, restoran, pencucian kendaraan) — pemakaian >1.000 m³/bulan.<br>
                    <strong>Kelompok 5:</strong> Pengguna kebutuhan dasar (usaha rumah tangga kecil, hotel non-bintang, klinik).
                </div>
            </div>

            <div class="calc-form-group">
                <label>Volume Pemakaian Air (m³)</label>
                <input type="text" id="volume" placeholder="Contoh: 1.500,5" inputmode="decimal"
                       oninput="this.value = this.value.replace(/[^0-9.,]/g, '').replace(/(\\,[0-9]{2})./g, '$1');">
                <div class="help-text">Gunakan titik untuk ribuan, koma untuk desimal (format Indonesia)</div>
            </div>

            <button class="calc-btn" onclick="calculateWaterTax()">
                <i class="bi bi-calculator"></i> Hitung Pajak Air Tanah
            </button>

            <div class="calc-result" id="calcResult">
                <div class="calc-result-header">
                    <h3><i class="bi bi-droplet-half"></i> Rincian Perhitungan</h3>
                </div>
                <table class="calc-result-table">
                    <thead>
                        <tr>
                            <th>Tier</th>
                            <th>Rentang Volume</th>
                            <th style="text-align:right">Volume (m³)</th>
                            <th style="text-align:right">HDA / NPA (Rp/m³)</th>
                            <th style="text-align:right">NPA (Rp)</th>
                        </tr>
                    </thead>
                    <tbody id="tierTableBody"></tbody>
                </table>
                <div class="calc-result-summary">
                    <div class="calc-result-row">
                        <span class="label">Total NPA (Nilai Perolehan Air)</span>
                        <span class="value" id="resTotalNPA">-</span>
                    </div>
                    <div class="calc-result-row">
                        <span class="label">Tarif Pajak</span>
                        <span class="value">20%</span>
                    </div>
                    <div class="calc-result-total">
                        <span class="label">Estimasi Pajak Air Tanah</span>
                        <span class="value" id="resTotalPajak">-</span>
                    </div>
                </div>
            </div>

            <div class="calc-disclaimer">
                <strong><i class="bi bi-info-circle"></i> Disclaimer:</strong>
                Perhitungan ini bersifat estimasi berdasarkan pergub. Nilai pajak final tetap akan diputuskan oleh petugas verifikasi BAPENDA.
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
// ========================================
// Kalkulator Pajak Air Tanah — JS (mirroring Flutter logic)
// ========================================

// Rate table [kelompok1, kelompok2, kelompok3, kelompok4, kelompok5]
const RATES = {
    'lama': [
        [4300, 3700, 3100, 2600, 2000],  // Tier 1: 0-50 m³
        [5600, 4700, 3850, 3000, 2100],  // Tier 2: 51-500 m³
        [7500, 6200, 4900, 3600, 2350],  // Tier 3: 501-1000 m³
        [10400, 8400, 6500, 4600, 2700], // Tier 4: 1001-2500 m³
        [14700, 11800, 8900, 6000, 3100] // Tier 5: >2500 m³
    ],
    'baru_baik_ada': [
        [29674, 27875, 26077, 24278, 22480],
        [33720, 31022, 28325, 25627, 22930],
        [39790, 35743, 31697, 27650, 23604],
        [48894, 42824, 36755, 30685, 24616],
        [62539, 53457, 44331, 35249, 26122]
    ],
    'baru_baik_tidak': [
        [20232, 18434, 16635, 14837, 13038],
        [24278, 21581, 18883, 16186, 13488],
        [30348, 26302, 22255, 18209, 14162],
        [39452, 33383, 27313, 21244, 15174],
        [53098, 44016, 34889, 25807, 16680]
    ],
    'baru_tidak_ada': [
        [13488, 11690, 9891, 8093, 6294],
        [17534, 14837, 12139, 9442, 6744],
        [23604, 19558, 15511, 11465, 7418],
        [32708, 26639, 20569, 14500, 8430],
        [46354, 37272, 28145, 19063, 9936]
    ],
    'baru_tidak_tidak': [
        [9442, 7643, 5845, 4046, 2248],
        [13488, 10790, 8093, 5395, 2698],
        [19558, 15511, 11465, 7418, 3372],
        [28662, 22592, 16523, 10453, 4384],
        [42307, 33225, 24099, 15017, 5890]
    ]
};

const TIER_SPANS = [50, 450, 500, 1500, Infinity];
const TIER_LABELS = ['0 – 50', '51 – 500', '501 – 1.000', '1.001 – 2.500', '> 2.500'];
const TAX_RATE = 0.20; // 20%

function toggleKelompokInfo() {
    document.getElementById('kelompokInfo').classList.toggle('visible');
}

function parseIndonesianNumber(str) {
    // Indonesian format: 1.500,5 = 1500.5
    str = str.replace(/\./g, '').replace(',', '.');
    return parseFloat(str) || 0;
}

function formatCurrency(amount) {
    return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
}

function formatNumber(n) {
    return n.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

function calculateWaterTax() {
    const scenarioId = document.getElementById('scenario').value;
    const kelompokIdx = parseInt(document.getElementById('kelompok').value);
    let volume = parseIndonesianNumber(document.getElementById('volume').value);
    
    // Batasi maksimal 2 angka di belakang koma
    volume = Math.round(volume * 100) / 100;

    if (!volume || volume <= 0) {
        alert('Harap masukkan volume pemakaian air.');
        return;
    }

    let remaining = volume;
    let totalNPA = 0;
    let html = '';

    const selectedRates = RATES[scenarioId];

    for (let i = 0; i < 5; i++) {
        const span = TIER_SPANS[i];
        const used = Math.min(remaining, span);
        const rate = selectedRates[i][kelompokIdx];

        if (used > 0) {
            const npa = used * rate;
            totalNPA += npa;

            html += `<tr>
                <td>Tier ${i + 1}</td>
                <td>${TIER_LABELS[i]} m³</td>
                <td class="num">${formatNumber(used)}</td>
                <td class="num">${rate.toLocaleString('id-ID')}</td>
                <td class="num">${formatCurrency(npa)}</td>
            </tr>`;
        }

        remaining -= used;
        if (remaining <= 0) break;
    }

    const totalPajak = totalNPA * TAX_RATE;

    document.getElementById('tierTableBody').innerHTML = html;
    document.getElementById('resTotalNPA').textContent = formatCurrency(totalNPA);
    document.getElementById('resTotalPajak').textContent = formatCurrency(totalPajak);
    document.getElementById('calcResult').classList.add('visible');
}
</script>
@endsection
