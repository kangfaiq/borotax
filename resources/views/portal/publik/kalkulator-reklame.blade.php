@extends('layouts.portal-guest')

@section('title', 'Kalkulator Pajak Reklame - Borotax')

@section('navbar-class', 'navbar-light')

@section('nav-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/kalkulator-reklame') }}" style="color: var(--primary-dark); font-weight: 600;">Kalkulator Reklame</a>
    <a href="{{ url('/login') }}" class="btn btn-primary btn-sm navbar-cta">Login</a>
@endsection

@section('nav-mobile-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/kalkulator-reklame') }}">Kalkulator Reklame</a>
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
    max-width: 760px;
    margin: 0 auto;
    box-shadow: var(--shadow-md);
}

.calc-section-title {
    font-size: 0.9rem; font-weight: 700; color: var(--text-primary);
    margin: 24px 0 12px; padding-bottom: 8px; border-bottom: 2px solid var(--primary-50);
    display: flex; align-items: center; gap: 8px;
}
.calc-section-title:first-child { margin-top: 0; }
.calc-section-title i { color: var(--primary); }

.calc-form-group { margin-bottom: 16px; }
.calc-form-group label {
    display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;
}
.calc-form-group select,
.calc-form-group input[type="text"],
.calc-form-group input[type="number"] {
    width: 100%; padding: 11px 14px; border: 1px solid var(--border);
    border-radius: var(--radius-md); font-family: inherit; font-size: 0.88rem;
    color: var(--text-primary); background: var(--bg-surface-variant);
    transition: border-color var(--transition); outline: none;
}
.calc-form-group select:focus,
.calc-form-group input:focus { border-color: var(--primary); }

.calc-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.calc-form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }

.lokasi-picker {
    padding: 14px 16px; border: 1px solid var(--border); border-radius: var(--radius-md);
    background: var(--bg-surface-variant); cursor: pointer; transition: all var(--transition);
    display: flex; align-items: center; gap: 12px;
}
.lokasi-picker:hover { border-color: var(--primary); }
.lokasi-picker .icon { color: var(--primary); font-size: 1.2rem; }
.lokasi-picker .text { flex: 1; }
.lokasi-picker .text .main { font-size: 0.88rem; font-weight: 500; color: var(--text-primary); }
.lokasi-picker .text .sub { font-size: 0.75rem; color: var(--primary); font-weight: 600; margin-top: 2px; }
.lokasi-picker .arrow { color: var(--text-tertiary); }

/* Modal for lokasi picker */
.lokasi-modal-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4);
    z-index: 2000; align-items: flex-end; justify-content: center;
}
.lokasi-modal-overlay.active { display: flex; }
.lokasi-modal {
    background: var(--bg-card); border-radius: var(--radius-xl) var(--radius-xl) 0 0;
    width: 100%; max-width: 600px; max-height: 80vh; overflow: hidden;
    display: flex; flex-direction: column; animation: slideUp 0.3s ease-out;
}
@keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
.lokasi-modal-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 20px 24px 12px; border-bottom: 1px solid var(--border);
}
.lokasi-modal-header h3 { font-size: 1.05rem; font-weight: 700; }
.lokasi-modal-header button {
    background: none; border: none; cursor: pointer; font-size: 1.3rem; color: var(--text-tertiary);
    padding: 4px; border-radius: var(--radius-sm);
}
.lokasi-search {
    padding: 12px 24px;
}
.lokasi-search input {
    width: 100%; padding: 10px 14px 10px 36px; border: 1px solid var(--border);
    border-radius: var(--radius-md); font-size: 0.88rem; font-family: inherit;
    outline: none; background: var(--bg-surface-variant);
}
.lokasi-search { position: relative; }
.lokasi-search i { position: absolute; left: 36px; top: 50%; transform: translateY(-50%); color: var(--text-tertiary); }
.lokasi-list { overflow-y: auto; padding: 0 24px 24px; flex: 1; }
.lokasi-group-label {
    font-size: 0.78rem; font-weight: 700; color: var(--primary); padding: 10px 0 4px;
    text-transform: uppercase; letter-spacing: 0.03em;
}
.lokasi-item {
    padding: 10px 12px; border-radius: var(--radius-sm); cursor: pointer;
    font-size: 0.88rem; color: var(--text-primary); transition: background var(--transition);
}
.lokasi-item:hover { background: var(--primary-50); }

.calc-result {
    margin-top: 24px; border-radius: var(--radius-lg);
    background: linear-gradient(135deg, #1E293B 0%, #334155 100%);
    color: white; padding: 24px;
}
.calc-result h3 { font-size: 1rem; font-weight: 700; margin-bottom: 16px; opacity: 0.9; }
.calc-result-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,0.08);
}
.calc-result-row:last-child { border-bottom: none; }
.calc-result-row .label { font-size: 0.82rem; opacity: 0.75; }
.calc-result-row .value { font-size: 0.88rem; font-weight: 700; }
.calc-result-total {
    margin-top: 10px; padding-top: 12px; border-top: 2px solid rgba(255,255,255,0.2);
    display: flex; justify-content: space-between; align-items: center;
}
.calc-result-total .label { font-size: 0.95rem; font-weight: 700; }
.calc-result-total .value { font-size: 1.3rem; font-weight: 800; color: #FCD34D; }

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

.luas-display {
    font-size: 0.78rem; color: var(--primary); font-weight: 600; margin-top: 4px;
}

@media (max-width: 768px) {
    .calc-box { padding: 20px 16px; }
    .calc-form-row, .calc-form-row-3 { grid-template-columns: 1fr; }
    .lokasi-modal { max-width: 100%; }
}
</style>
@endsection

@section('content')
<section class="calc-page">
    <div class="container">
        <div class="calc-header">
            <span class="section-badge"><i class="bi bi-aspect-ratio"></i> LAYANAN PUBLIK</span>
            <h1>Kalkulator Pajak Reklame</h1>
            <p>Estimasi pajak reklame berdasarkan lokasi, jenis, dan dimensi reklame di Kab. Bojonegoro</p>
        </div>

        @include('portal.publik._nav', ['active' => 'kalkulator-reklame'])

        <div class="calc-box">
            {{-- Section 1: Lokasi --}}
            <div class="calc-section-title" style="margin-top:0"><i class="bi bi-geo-alt"></i> Lokasi Pemasangan</div>
            <div class="lokasi-picker" onclick="openLokasiModal()">
                <span class="icon"><i class="bi bi-geo-alt-fill"></i></span>
                <div class="text">
                    <div class="main" id="lokasiDisplay">Pilih Lokasi / Jalan</div>
                    <div class="sub" id="kelompokDisplay" style="display:none"></div>
                </div>
                <span class="arrow"><i class="bi bi-chevron-right"></i></span>
            </div>

            {{-- Section 2: Detail Reklame --}}
            <div class="calc-section-title"><i class="bi bi-card-image"></i> Detail Reklame</div>

            <div class="calc-form-group">
                <label>Jenis Reklame</label>
                <select id="jenisReklame" onchange="onJenisChange()"></select>
            </div>

            <div class="calc-form-row">
                <div class="calc-form-group">
                    <label>Bentuk Reklame</label>
                    <select id="bentukReklame" onchange="onBentukChange()">
                        <option value="persegi">Persegi / Persegi Panjang</option>
                        <option value="lingkaran">Lingkaran</option>
                        <option value="elips">Elips</option>
                        <option value="trapesium">Trapesium</option>
                        <option value="segitiga">Segitiga</option>
                    </select>
                </div>
                <div class="calc-form-group">
                    <label>Satuan Waktu</label>
                    <select id="satuanMasa" onchange="calculate()"></select>
                </div>
            </div>

            {{-- Dynamic dimension fields --}}
            <div id="dimPersegiFields" class="calc-form-row">
                <div class="calc-form-group">
                    <label>Panjang (m)</label>
                    <input type="number" id="dimPanjang" value="1" min="0.1" step="0.1" oninput="calculate()">
                </div>
                <div class="calc-form-group">
                    <label>Lebar (m)</label>
                    <input type="number" id="dimLebar" value="1" min="0.1" step="0.1" oninput="calculate()">
                </div>
            </div>
            <div id="dimLingkaranFields" style="display:none">
                <div class="calc-form-group">
                    <label>Diameter (m)</label>
                    <input type="number" id="dimDiameter" value="1" min="0.1" step="0.1" oninput="calculate()">
                </div>
            </div>
            <div id="dimElipsFields" style="display:none">
                <div class="calc-form-row">
                    <div class="calc-form-group">
                        <label>Diameter 1 (m)</label>
                        <input type="number" id="dimDiameter1" value="1" min="0.1" step="0.1" oninput="calculate()">
                    </div>
                    <div class="calc-form-group">
                        <label>Diameter 2 (m)</label>
                        <input type="number" id="dimDiameter2" value="1" min="0.1" step="0.1" oninput="calculate()">
                    </div>
                </div>
            </div>
            <div id="dimTrapesiumFields" style="display:none">
                <div class="calc-form-row-3">
                    <div class="calc-form-group">
                        <label>Sisi Atas (m)</label>
                        <input type="number" id="dimAtas" value="1" min="0.1" step="0.1" oninput="calculate()">
                    </div>
                    <div class="calc-form-group">
                        <label>Sisi Bawah (m)</label>
                        <input type="number" id="dimBawah" value="1" min="0.1" step="0.1" oninput="calculate()">
                    </div>
                    <div class="calc-form-group">
                        <label>Tinggi (m)</label>
                        <input type="number" id="dimTinggi" value="1" min="0.1" step="0.1" oninput="calculate()">
                    </div>
                </div>
            </div>
            <div id="dimSegitigaFields" style="display:none">
                <div class="calc-form-row">
                    <div class="calc-form-group">
                        <label>Alas (m)</label>
                        <input type="number" id="dimAlas" value="1" min="0.1" step="0.1" oninput="calculate()">
                    </div>
                    <div class="calc-form-group">
                        <label>Tinggi (m)</label>
                        <input type="number" id="dimTinggiSegitiga" value="1" min="0.1" step="0.1" oninput="calculate()">
                    </div>
                </div>
            </div>
            <div class="luas-display" id="luasDisplay">Luas: 1,00 m²</div>

            <div class="calc-form-row-3" style="margin-top: 12px;">
                <div class="calc-form-group">
                    <label>Jumlah Sisi/Muka</label>
                    <input type="number" id="jumlahMuka" value="1" min="1" oninput="calculate()">
                </div>
                <div class="calc-form-group">
                    <label>Durasi</label>
                    <input type="number" id="durasi" value="1" min="1" oninput="calculate()">
                </div>
                <div class="calc-form-group">
                    <label>Jumlah Reklame</label>
                    <input type="number" id="jumlahReklame" value="1" min="1" oninput="calculate()">
                </div>
            </div>

            {{-- Section 3: Faktor Penyesuaian --}}
            <div class="calc-section-title"><i class="bi bi-sliders"></i> Faktor Penyesuaian</div>

            <div class="calc-form-row">
                <div class="calc-form-group">
                    <label>Lokasi Penempatan</label>
                    <select id="jenisLokasi" onchange="calculate()">
                        <option value="1.0">Luar Ruangan (100%)</option>
                        <option value="0.25">Dalam Ruangan (25%)</option>
                    </select>
                </div>
                <div class="calc-form-group">
                    <label>Jenis Produk</label>
                    <select id="jenisProduk" onchange="calculate()">
                        <option value="1.0">Non-Rokok (100%)</option>
                        <option value="1.1">Produk Rokok (110%)</option>
                    </select>
                </div>
            </div>

            {{-- Result --}}
            <div class="calc-result" id="calcResult">
                <h3><i class="bi bi-receipt-cutoff"></i> Estimasi Pajak Reklame</h3>
                <div class="calc-result-row">
                    <span class="label">Tarif per Satuan</span>
                    <span class="value" id="resTarif">-</span>
                </div>
                <div class="calc-result-row">
                    <span class="label">Luas Total</span>
                    <span class="value" id="resLuas">-</span>
                </div>
                <div class="calc-result-row">
                    <span class="label">Pokok Pajak Dasar</span>
                    <span class="value" id="resPokok">-</span>
                </div>
                <div class="calc-result-row" id="rowPenyesuaian" style="display:none">
                    <span class="label">Setelah Penyesuaian</span>
                    <span class="value" id="resPenyesuaian">-</span>
                </div>
                <div class="calc-result-row" id="rowNilaiStrategis" style="display:none">
                    <span class="label" id="resNSLabel">Nilai Strategis</span>
                    <span class="value" id="resNilaiStrategis">-</span>
                </div>
                <div class="calc-result-total">
                    <span class="label">Total Estimasi Pajak</span>
                    <span class="value" id="resTotal">Rp 0</span>
                </div>
            </div>

            <div class="calc-disclaimer">
                <strong><i class="bi bi-info-circle"></i> Disclaimer:</strong>
                Perhitungan ini bersifat estimasi. Nilai final ditentukan oleh petugas verifikasi BAPENDA Kabupaten Bojonegoro.
            </div>
        </div>
    </div>
</section>

{{-- Lokasi Modal --}}
<div class="lokasi-modal-overlay" id="lokasiModalOverlay" onclick="closeLokasiModal(event)">
    <div class="lokasi-modal" onclick="event.stopPropagation()">
        <div class="lokasi-modal-header">
            <h3>Pilih Lokasi</h3>
            <button onclick="closeLokasiModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="lokasi-search">
            <i class="bi bi-search"></i>
            <input type="text" id="lokasiSearch" placeholder="Cari nama jalan..." oninput="filterLokasi()">
        </div>
        <div class="lokasi-list" id="lokasiList"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ========================================
// Kalkulator Pajak Reklame — Complete JS
// ========================================

// === TARIFF DATA (dynamic from database) ===
const TARIF_DATA = @json($tarifData);

// === LOCATION DATA (dynamic from database) ===
const LOKASI_DATA = @json($lokasiData);

// === NILAI STRATEGIS CONFIG (dynamic from database) ===
const NS_RATES = @json($nsRates);

const REKLAME_TETAP_FOR_NS = @json($reklameTetapForNs);

// === STATE ===
let selectedKelompok = null;
let selectedLokasiName = null;
let selectedTarif = null;
let selectedSatuanId = null;

// === BUILD UNIQUE JENIS LIST ===
function getUniqueJenisList() {
    const seen = new Set();
    const items = [];
    for (const t of TARIF_DATA) {
        const key = `${t.nama}-${t.sub || ''}`;
        if (!seen.has(key)) {
            seen.add(key);
            items.push(t);
        }
    }
    return items;
}

function getAvailableSatuan(nama, sub) {
    return TARIF_DATA.filter(t => t.nama === nama && (t.sub || '') === (sub || ''));
}

function getActiveTarif(nama, sub, satuan) {
    return TARIF_DATA.find(t => t.nama === nama && (t.sub || '') === (sub || '') && t.satuan === satuan) || null;
}

function getTarifValue(item, kelompok) {
    if (item.tarifTunggal != null) return item.tarifTunggal;
    if (item.tarifPerKelompok && kelompok) return item.tarifPerKelompok[kelompok] || 0;
    return 0;
}

function formatCurrency(amount) {
    return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
}

// === INIT ===
function init() {
    const select = document.getElementById('jenisReklame');
    const uniqueItems = getUniqueJenisList();
    select.innerHTML = '';

    // Group: Tetap first, then Insidentil
    const tetap = uniqueItems.filter(i => !i.is_insidentil);
    const insidentil = uniqueItems.filter(i => i.is_insidentil);

    const addOptions = (items, groupLabel) => {
        const optgroup = document.createElement('optgroup');
        optgroup.label = groupLabel;
        items.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.nama + (item.sub ? ` (${item.sub})` : '');
            optgroup.appendChild(opt);
        });
        select.appendChild(optgroup);
    };

    addOptions(tetap, 'Reklame Tetap');
    addOptions(insidentil, 'Reklame Insidentil');

    selectedTarif = uniqueItems[0];
    updateSatuanOptions();
    calculate();
}

function onJenisChange() {
    const id = document.getElementById('jenisReklame').value;
    selectedTarif = TARIF_DATA.find(t => t.id === id);
    updateSatuanOptions();
    calculate();
}

function updateSatuanOptions() {
    if (!selectedTarif) return;
    const variants = getAvailableSatuan(selectedTarif.nama, selectedTarif.sub);
    const select = document.getElementById('satuanMasa');
    select.innerHTML = '';
    variants.forEach(v => {
        const opt = document.createElement('option');
        opt.value = v.satuan;
        opt.textContent = v.satuanLabel;
        select.appendChild(opt);
    });
    // Select the first available
    selectedSatuanId = variants.length > 0 ? variants[0].satuan : null;
    select.value = selectedSatuanId;
}

function onBentukChange() {
    const bentuk = document.getElementById('bentukReklame').value;
    document.getElementById('dimPersegiFields').style.display = bentuk === 'persegi' ? '' : 'none';
    document.getElementById('dimLingkaranFields').style.display = bentuk === 'lingkaran' ? '' : 'none';
    document.getElementById('dimElipsFields').style.display = bentuk === 'elips' ? '' : 'none';
    document.getElementById('dimTrapesiumFields').style.display = bentuk === 'trapesium' ? '' : 'none';
    document.getElementById('dimSegitigaFields').style.display = bentuk === 'segitiga' ? '' : 'none';
    calculate();
}

function getLuas() {
    const bentuk = document.getElementById('bentukReklame').value;
    if (bentuk === 'persegi') {
        const p = parseFloat(document.getElementById('dimPanjang').value) || 0;
        const l = parseFloat(document.getElementById('dimLebar').value) || 0;
        return p * l;
    } else if (bentuk === 'lingkaran') {
        const d = parseFloat(document.getElementById('dimDiameter').value) || 0;
        const r = d / 2;
        return Math.PI * r * r;
    } else if (bentuk === 'elips') {
        const d1 = parseFloat(document.getElementById('dimDiameter1').value) || 0;
        const d2 = parseFloat(document.getElementById('dimDiameter2').value) || 0;
        return Math.PI * (d1 / 2) * (d2 / 2);
    } else if (bentuk === 'segitiga') {
        const alas = parseFloat(document.getElementById('dimAlas').value) || 0;
        const tinggi = parseFloat(document.getElementById('dimTinggiSegitiga').value) || 0;
        return 0.5 * alas * tinggi;
    } else {
        const a = parseFloat(document.getElementById('dimAtas').value) || 0;
        const b = parseFloat(document.getElementById('dimBawah').value) || 0;
        const h = parseFloat(document.getElementById('dimTinggi').value) || 0;
        return ((a + b) / 2) * h;
    }
}

function calculate() {
    const luas = getLuas();
    document.getElementById('luasDisplay').textContent = `Luas: ${luas.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})} m²`;

    if (!selectedTarif) return;

    const satuanVal = document.getElementById('satuanMasa').value;
    const activeTarif = getActiveTarif(selectedTarif.nama, selectedTarif.sub, satuanVal);
    if (!activeTarif) return;

    const tarif = getTarifValue(activeTarif, selectedKelompok);
    const jumlahMuka = parseInt(document.getElementById('jumlahMuka').value) || 1;
    const durasi = parseInt(document.getElementById('durasi').value) || 1;
    const jumlahReklame = parseInt(document.getElementById('jumlahReklame').value) || 1;
    const multiplierLokasi = parseFloat(document.getElementById('jenisLokasi').value);
    const multiplierProduk = parseFloat(document.getElementById('jenisProduk').value);

    // 1. Pokok Pajak Dasar
    const pokokDasar = tarif * luas * jumlahMuka * durasi * jumlahReklame;

    // 2. Penyesuaian
    const pokokSetelahPenyesuaian = pokokDasar * multiplierLokasi * multiplierProduk;

    // 3. Nilai Strategis
    let nilaiStrategis = 0;
    let kelasStrategis = null;
    const isReklameTetap = !activeTarif.is_insidentil;
    const isAllowedForNS = REKLAME_TETAP_FOR_NS.includes(activeTarif.nama);

    if (isReklameTetap && isAllowedForNS && selectedKelompok) {
        // Map kelompok to kelas
        if (['A','A1','A2','A3'].includes(selectedKelompok)) kelasStrategis = 'A';
        else if (selectedKelompok === 'B') kelasStrategis = 'B';
        else kelasStrategis = 'C';

        const isPerTahun = satuanVal === 'perTahun';
        const isPerBulan = satuanVal === 'perBulan';

        if ((isPerTahun || isPerBulan) && luas >= 10) {
            const period = isPerTahun ? 'tahun' : 'bulan';
            const sizeKey = luas >= 25 ? 'big' : 'med';
            const nsValue = NS_RATES[kelasStrategis][sizeKey][period];
            nilaiStrategis = nsValue * durasi * jumlahReklame;
        } else {
            kelasStrategis = null;
        }
    }

    const totalPajak = pokokSetelahPenyesuaian + nilaiStrategis;

    // Update UI
    document.getElementById('resTarif').textContent = formatCurrency(tarif) + ' / ' + (activeTarif.satuanLabel || '');
    document.getElementById('resLuas').textContent = luas.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' m² × ' + jumlahMuka + ' muka';
    document.getElementById('resPokok').textContent = formatCurrency(pokokDasar);

    const showPenyesuaian = multiplierLokasi !== 1.0 || multiplierProduk !== 1.0;
    document.getElementById('rowPenyesuaian').style.display = showPenyesuaian ? '' : 'none';
    document.getElementById('resPenyesuaian').textContent = formatCurrency(pokokSetelahPenyesuaian);

    document.getElementById('rowNilaiStrategis').style.display = kelasStrategis ? '' : 'none';
    if (kelasStrategis) {
        document.getElementById('resNSLabel').textContent = `Nilai Strategis (Kelas ${kelasStrategis})`;
        document.getElementById('resNilaiStrategis').textContent = formatCurrency(nilaiStrategis);
    }

    document.getElementById('resTotal').textContent = formatCurrency(totalPajak);
}

// === LOKASI MODAL ===
function openLokasiModal() {
    document.getElementById('lokasiModalOverlay').classList.add('active');
    document.getElementById('lokasiSearch').value = '';
    renderLokasiList('');
    document.getElementById('lokasiSearch').focus();
}

function closeLokasiModal(e) {
    if (e && e.target !== document.getElementById('lokasiModalOverlay')) return;
    document.getElementById('lokasiModalOverlay').classList.remove('active');
}

function filterLokasi() {
    const q = document.getElementById('lokasiSearch').value.toLowerCase();
    renderLokasiList(q);
}

function renderLokasiList(query) {
    const container = document.getElementById('lokasiList');
    let html = '';
    for (const [kelompok, data] of Object.entries(LOKASI_DATA)) {
        const filtered = query ? data.streets.filter(s => s.toLowerCase().includes(query)) : data.streets;
        if (filtered.length === 0) continue;
        html += `<div class="lokasi-group-label">${data.label} (${data.desc})</div>`;
        filtered.forEach(street => {
            html += `<div class="lokasi-item" onclick="selectLokasi('${street.replace(/'/g,"\\'")}','${kelompok}')">${street}</div>`;
        });
    }
    container.innerHTML = html || '<div style="padding:20px;text-align:center;color:var(--text-tertiary)">Tidak ditemukan</div>';
}

function selectLokasi(name, kelompok) {
    selectedLokasiName = name;
    selectedKelompok = kelompok;
    document.getElementById('lokasiDisplay').textContent = name;
    document.getElementById('lokasiDisplay').style.fontWeight = '700';
    const kelData = LOKASI_DATA[kelompok];
    document.getElementById('kelompokDisplay').textContent = `${kelData.label} — ${kelData.desc}`;
    document.getElementById('kelompokDisplay').style.display = '';
    document.getElementById('lokasiModalOverlay').classList.remove('active');
    calculate();
}

// Init on load
document.addEventListener('DOMContentLoaded', init);
</script>
@endsection
