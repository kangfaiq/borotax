@extends('layouts.portal-guest')

@section('title', 'Kalkulator Sanksi - Borotax')

@section('navbar-class', 'navbar-light')

@section('nav-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/kalkulator-sanksi') }}" style="color: var(--primary-dark); font-weight: 600;">Kalkulator Sanksi</a>
    <a href="{{ url('/login') }}" class="btn btn-primary btn-sm navbar-cta">Login</a>
@endsection

@section('nav-mobile-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/kalkulator-sanksi') }}">Kalkulator Sanksi</a>
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
    max-width: 640px;
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

.calc-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

.calc-btn {
    width: 100%; padding: 14px; border: none; border-radius: var(--radius-full);
    background: var(--primary); color: white; font-weight: 700; font-size: 0.95rem;
    cursor: pointer; transition: all var(--transition); font-family: inherit;
    margin-top: 8px;
}
.calc-btn:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: var(--shadow-primary); }

.calc-result {
    margin-top: 28px; padding: 24px; border-radius: var(--radius-lg);
    background: linear-gradient(135deg, #1E293B 0%, #334155 100%);
    color: white; display: none;
}
.calc-result.visible { display: block; animation: fadeInUp 0.4s ease-out; }
.calc-result h3 { font-size: 1rem; font-weight: 700; margin-bottom: 16px; opacity: 0.9; }
.calc-result-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.1);
}
.calc-result-row:last-child { border-bottom: none; }
.calc-result-row .label { font-size: 0.85rem; opacity: 0.8; }
.calc-result-row .value { font-size: 0.9rem; font-weight: 700; }
.calc-result-total {
    margin-top: 12px; padding-top: 12px; border-top: 2px solid rgba(255,255,255,0.2);
    display: flex; justify-content: space-between; align-items: center;
}
.calc-result-total .label { font-size: 0.95rem; font-weight: 700; }
.calc-result-total .value { font-size: 1.2rem; font-weight: 800; color: #FCD34D; }

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
    .calc-form-row { grid-template-columns: 1fr; }
}
</style>
@endsection

@section('content')
<section class="calc-page">
    <div class="container">
        <div class="calc-header">
            <span class="section-badge"><i class="bi bi-calculator"></i> LAYANAN PUBLIK</span>
            <h1>Kalkulator Sanksi</h1>
            <p>Estimasi denda/sanksi keterlambatan pembayaran pajak daerah</p>
        </div>

        @include('portal.publik._nav', ['active' => 'kalkulator-sanksi'])

        <div class="calc-box">
            <div class="calc-form-group">
                <label>Jenis Pajak</label>
                <select id="jenisPajak">
                    <option value="selfAssessment">PBJT (Self Assessment)</option>
                    <option value="reklame">Pajak Reklame</option>
                    <option value="airTanah">Pajak Air Tanah</option>
                </select>
            </div>

            <div class="calc-form-group">
                <label>Nominal Pokok Pajak (Rp)</label>
                <input type="text" id="nominalPajak" placeholder="Contoh: 1.000.000" inputmode="numeric">
            </div>

            <div class="calc-form-row">
                <div class="calc-form-group">
                    <label id="masaPajakLabel">Masa Pajak</label>
                    <input type="month" id="masaPajak" value="2025-01">
                </div>
                <div class="calc-form-group">
                    <label>Rencana Bayar</label>
                    <input type="date" id="rencanaBayar">
                </div>
            </div>

            <button class="calc-btn" onclick="calculatePenalty()">
                <i class="bi bi-calculator"></i> Hitung Sanksi
            </button>

            <div class="calc-result" id="calcResult">
                <h3><i class="bi bi-receipt-cutoff"></i> Hasil Estimasi</h3>
                <div class="calc-result-row">
                    <span class="label">Jatuh Tempo</span>
                    <span class="value" id="resJatuhTempo">-</span>
                </div>
                <div class="calc-result-row">
                    <span class="label">Keterlambatan</span>
                    <span class="value" id="resKeterlambatan">-</span>
                </div>
                <div class="calc-result-row">
                    <span class="label">Tarif Sanksi</span>
                    <span class="value" id="resTarif">-</span>
                </div>
                <div class="calc-result-row">
                    <span class="label">Pokok Pajak</span>
                    <span class="value" id="resPokok">-</span>
                </div>
                <div class="calc-result-row">
                    <span class="label">Estimasi Denda</span>
                    <span class="value" id="resDenda">-</span>
                </div>
                <div class="calc-result-total">
                    <span class="label">Estimasi Total Bayar</span>
                    <span class="value" id="resTotal">-</span>
                </div>
            </div>

            <div class="calc-disclaimer">
                <strong><i class="bi bi-info-circle"></i> Disclaimer:</strong>
                Perhitungan ini bersifat estimasi. Nilai final ditentukan oleh petugas verifikasi BAPENDA Kabupaten Bojonegoro.
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
// ========================================
// Kalkulator Sanksi Pajak — JS (mirroring Flutter logic)
// ========================================

// National holidays (YYYYMMDD)
const HOLIDAYS = [
    // 2024
    20240101,20240208,20240209,20240210,20240214,
    20240311,20240312,20240329,20240408,20240409,
    20240410,20240411,20240412,20240415,20240501,
    20240509,20240510,20240523,20240524,20240601,
    20240617,20240618,20240707,20240817,20240916,
    20241225,20241226,
    // 2025 (Estimasi)
    20250101,20250127,20250129,20250329,20250331,
    20250401,20250418,20250501,20250512,20250529,
    20250601,20250607,20250627,20250817,20250905,
    20251225,20251226,
    // 2026 (Estimasi)
    20260101,20260116,20260217,20260319,20260320,20260321,
    20260323,20260324,20260325,20260403,20260501,20260514,
    20260527,20260531,20260601,20260616,20260817,20260825,
    20261225,20261226,
];

function dateToInt(d) {
    return d.getFullYear() * 10000 + (d.getMonth() + 1) * 100 + d.getDate();
}

function isHoliday(d) {
    return HOLIDAYS.includes(dateToInt(d));
}

function isWeekend(d) {
    const day = d.getDay();
    return day === 0 || day === 6;
}

function isWorkingDay(d) {
    return !isWeekend(d) && !isHoliday(d);
}

function endOfMonth(year, month) {
    return new Date(year, month, 0); // month is 1-based here, 0 = last day of prev
}

function getNthWorkingDay(year, month, n) {
    // month is 1-based
    let date = new Date(year, month - 1, 1);
    let count = 0;
    while (count < n) {
        if (isWorkingDay(date)) count++;
        if (count < n) date.setDate(date.getDate() + 1);
    }
    return date;
}

function getJatuhTempo(jenisPajak, masaPajak) {
    const year = masaPajak.getFullYear();
    const month = masaPajak.getMonth() + 1; // 1-based

    if (jenisPajak === 'selfAssessment') {
        if (year < 2024) {
            // End of following month
            const nextMonth = month + 1;
            const y = nextMonth > 12 ? year + 1 : year;
            const m = nextMonth > 12 ? 1 : nextMonth;
            return endOfMonth(y, m);
        } else if (year < 2025 || (year === 2025 && month <= 6)) {
            // 10th working day of following month
            const nextMonth = month + 1;
            const y = nextMonth > 12 ? year + 1 : year;
            const m = nextMonth > 12 ? 1 : nextMonth;
            return getNthWorkingDay(y, m, 10);
        } else {
            // Triwulan (quarterly) from Jul 2025
            // Q1(Jan-Mar)->Apr, Q2(Apr-Jun)->Jul, Q3(Jul-Sep)->Oct, Q4(Oct-Dec)->Jan+1
            let targetMonth, targetYear;
            if (month <= 3) { targetMonth = 4; targetYear = year; }
            else if (month <= 6) { targetMonth = 7; targetYear = year; }
            else if (month <= 9) { targetMonth = 10; targetYear = year; }
            else { targetMonth = 1; targetYear = year + 1; }
            return getNthWorkingDay(targetYear, targetMonth, 10);
        }
    } else if (jenisPajak === 'reklame') {
        // 1 month from start - 1 day
        const d = new Date(masaPajak);
        d.setMonth(d.getMonth() + 1);
        d.setDate(d.getDate() - 1);
        return d;
    } else {
        // Air Tanah: end of following month
        const nextMonth = month + 1;
        const y = nextMonth > 12 ? year + 1 : year;
        const m = nextMonth > 12 ? 1 : nextMonth;
        return endOfMonth(y, m);
    }
}

function monthDiff(from, to) {
    return (to.getFullYear() - from.getFullYear()) * 12 + (to.getMonth() - from.getMonth());
}

function formatCurrency(amount) {
    return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
}

function formatDate(d) {
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

// Input formatting
document.getElementById('nominalPajak').addEventListener('input', function(e) {
    let val = e.target.value.replace(/\D/g, '');
    if (val) val = parseInt(val).toLocaleString('id-ID');
    e.target.value = val;
});

// Dynamic label change
document.getElementById('jenisPajak').addEventListener('change', function() {
    const label = document.getElementById('masaPajakLabel');
    const input = document.getElementById('masaPajak');
    if (this.value === 'reklame') {
        label.textContent = 'Masa Awal Tayang';
        input.type = 'date';
    } else {
        label.textContent = 'Masa Pajak';
        input.type = 'month';
    }
});

// Set default rencana bayar to today
document.getElementById('rencanaBayar').valueAsDate = new Date();

function calculatePenalty() {
    const jenisPajak = document.getElementById('jenisPajak').value;
    const nominalStr = document.getElementById('nominalPajak').value.replace(/\D/g, '');
    const nominal = parseInt(nominalStr) || 0;
    const masaPajakInput = document.getElementById('masaPajak');
    const rencanaBayarInput = document.getElementById('rencanaBayar').value;

    if (!nominal || !rencanaBayarInput) {
        alert('Harap isi semua field terlebih dahulu.');
        return;
    }

    let masaPajak;
    if (jenisPajak === 'reklame') {
        masaPajak = new Date(masaPajakInput.value);
    } else {
        const [y, m] = masaPajakInput.value.split('-').map(Number);
        masaPajak = new Date(y, m - 1, 1);
    }

    const rencanaBayar = new Date(rencanaBayarInput);
    const jatuhTempo = getJatuhTempo(jenisPajak, masaPajak);

    // Check if late
    if (rencanaBayar <= jatuhTempo) {
        document.getElementById('calcResult').classList.add('visible');
        document.getElementById('resJatuhTempo').textContent = formatDate(jatuhTempo);
        document.getElementById('resKeterlambatan').textContent = '0 bulan (tidak terlambat)';
        document.getElementById('resTarif').textContent = '-';
        document.getElementById('resPokok').textContent = formatCurrency(nominal);
        document.getElementById('resDenda').textContent = formatCurrency(0);
        document.getElementById('resTotal').textContent = formatCurrency(nominal);
        return;
    }

    // Penalty rate
    const penaltyRate = jatuhTempo.getFullYear() < 2024 ? 0.02 : 0.01;
    const rateLabel = jatuhTempo.getFullYear() < 2024 ? '2% / bulan' : '1% / bulan';

    // Months late (min 1, max 24)
    let monthsLate = monthDiff(jatuhTempo, rencanaBayar);
    if (monthsLate < 1) monthsLate = 1;
    if (monthsLate > 24) monthsLate = 24;

    const penaltyAmount = nominal * penaltyRate * monthsLate;
    const totalAmount = nominal + penaltyAmount;

    // Show results
    document.getElementById('calcResult').classList.add('visible');
    document.getElementById('resJatuhTempo').textContent = formatDate(jatuhTempo);
    document.getElementById('resKeterlambatan').textContent = monthsLate + ' bulan';
    document.getElementById('resTarif').textContent = rateLabel;
    document.getElementById('resPokok').textContent = formatCurrency(nominal);
    document.getElementById('resDenda').textContent = formatCurrency(penaltyAmount);
    document.getElementById('resTotal').textContent = formatCurrency(totalAmount);
}
</script>
@endsection
