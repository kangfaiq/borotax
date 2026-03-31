@extends('layouts.portal-guest')

@section('title', 'Cek Status Permohonan Sewa Reklame - Borotax Portal')
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
    .cek-container {
        max-width: 700px;
        margin: 0 auto;
        padding: 100px 20px 40px;
    }

    .obj-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        color: var(--text-tertiary);
        margin-bottom: 20px;
        transition: color var(--transition);
    }

    .obj-back:hover { color: var(--primary-dark); }

    .cek-card {
        background: var(--bg-card);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: var(--shadow-md);
    }

    .cek-header {
        background: linear-gradient(140deg, #42A5F5 0%, #1565C0 100%);
        padding: 28px;
        color: #fff;
        text-align: center;
    }

    .cek-header h2 {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .cek-header p {
        font-size: 0.85rem;
        opacity: 0.85;
    }

    .cek-body {
        padding: 28px;
    }

    .cek-form {
        display: flex;
        gap: 12px;
        align-items: flex-end;
    }

    .cek-form .form-group {
        flex: 1;
    }

    .form-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 6px;
        display: block;
    }

    .form-input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        font-size: 0.9rem;
        background: var(--bg-surface-variant);
        color: var(--text-primary);
        transition: border-color var(--transition);
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(66, 165, 245, 0.15);
    }

    .btn-cek {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 12px 24px;
        background: linear-gradient(140deg, #42A5F5 0%, #1565C0 100%);
        color: #fff;
        border: none;
        border-radius: var(--radius-md);
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        transition: all var(--transition);
        white-space: nowrap;
    }

    .btn-cek:hover { box-shadow: var(--shadow-lg); transform: translateY(-1px); }

    /* Result card */
    .result-card {
        margin-top: 24px;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .result-card-header {
        padding: 18px 22px;
        display: flex;
        align-items: center;
        gap: 14px;
        border-bottom: 1px solid var(--border);
    }

    .result-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .result-icon.diajukan      { background: #E3F2FD; color: #1565C0; }
    .result-icon.perlu_revisi   { background: #E3F2FD; color: #0277BD; }
    .result-icon.diproses       { background: #FFF8E1; color: #F57F17; }
    .result-icon.disetujui      { background: #E8F5E9; color: #2E7D32; }
    .result-icon.ditolak        { background: #FFEBEE; color: #C62828; }

    .result-info h4 {
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .result-info .ri-sub {
        font-size: 0.78rem;
        color: var(--text-tertiary);
    }

    .result-status {
        margin-left: auto;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: var(--radius-full);
    }

    .result-status.diajukan      { background: #E3F2FD; color: #1565C0; }
    .result-status.perlu_revisi   { background: #E3F2FD; color: #0277BD; }
    .result-status.diproses       { background: #FFF8E1; color: #F57F17; }
    .result-status.disetujui      { background: #E8F5E9; color: #2E7D32; }
    .result-status.ditolak        { background: #FFEBEE; color: #C62828; }

    .result-card-body {
        padding: 18px 22px;
    }

    .result-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 14px;
    }

    .result-item .ri-label {
        font-size: 0.72rem;
        color: var(--text-tertiary);
        margin-bottom: 2px;
    }

    .result-item .ri-value {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .result-actions {
        padding: 16px 22px;
        border-top: 1px solid var(--border);
        display: flex;
        gap: 12px;
    }

    .btn-detail {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        background: linear-gradient(140deg, #42A5F5 0%, #1565C0 100%);
        color: #fff;
        border-radius: var(--radius-full);
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        transition: all var(--transition);
    }

    .btn-detail:hover { box-shadow: var(--shadow-lg); transform: translateY(-1px); color: #fff; }

    .not-found {
        margin-top: 24px;
        padding: 24px;
        text-align: center;
        background: #FFF3E0;
        border: 1px solid #FFCC80;
        border-radius: var(--radius-lg);
    }

    .not-found i { font-size: 2rem; color: #E65100; margin-bottom: 10px; }
    .not-found h4 { font-size: 0.92rem; font-weight: 700; color: #E65100; margin-bottom: 4px; }
    .not-found p { font-size: 0.82rem; color: #BF360C; }

    /* SKPD Info */
    .skpd-section {
        margin-top: 24px;
        border-radius: var(--radius-lg);
        border: 1px solid #C8E6C9;
        overflow: hidden;
    }

    .skpd-header {
        background: linear-gradient(140deg, #43A047 0%, #2E7D32 100%);
        padding: 20px 22px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .skpd-header i { font-size: 1.3rem; }
    .skpd-header h3 { font-size: 0.95rem; font-weight: 700; margin: 0; }
    .skpd-header p { font-size: 0.78rem; opacity: 0.85; margin: 2px 0 0; }

    .skpd-body { padding: 22px; }

    .skpd-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 18px;
    }

    .skpd-item .sk-label {
        font-size: 0.72rem;
        color: var(--text-tertiary);
        margin-bottom: 2px;
    }

    .skpd-item .sk-value {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .skpd-item.full { grid-column: 1 / -1; }

    .billing-box {
        background: linear-gradient(140deg, #1B5E20 0%, #2E7D32 100%);
        border-radius: var(--radius-lg);
        padding: 20px;
        color: #fff;
        text-align: center;
    }

    .billing-box .bb-label {
        font-size: 0.75rem;
        opacity: 0.8;
        margin-bottom: 4px;
    }

    .billing-box .bb-code {
        font-size: 1.4rem;
        font-weight: 800;
        letter-spacing: 2px;
        font-family: 'Courier New', monospace;
        margin-bottom: 4px;
    }

    .billing-box .bb-amount {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .btn-copy-billing {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 20px;
        background: rgba(255,255,255,0.2);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: var(--radius-full);
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition);
    }

    .btn-copy-billing:hover { background: rgba(255,255,255,0.3); }

    .skpd-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: var(--radius-full);
    }

    .skpd-status-badge.draft { background: #FFF3E0; color: #E65100; }
    .skpd-status-badge.disetujui { background: #E8F5E9; color: #2E7D32; }
    .skpd-status-badge.menunggu { background: #E3F2FD; color: #1565C0; }

    .skpd-note {
        margin-top: 14px;
        padding: 14px;
        background: #E8F5E9;
        border: 1px solid #C8E6C9;
        border-radius: var(--radius-md);
        font-size: 0.8rem;
        color: #2E7D32;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .skpd-note i { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }

    @media (max-width: 600px) {
        .cek-form { flex-direction: column; }
        .result-card-header { flex-wrap: wrap; }
        .result-status { margin-left: 0; }
        .skpd-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<div class="cek-container">
    <a href="{{ route('publik.sewa-reklame') }}" class="obj-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Aset
    </a>

    <div class="cek-card">
        <div class="cek-header">
            <h2><i class="bi bi-search"></i> Cek Status Permohonan</h2>
            <p>Masukkan nomor tiket untuk melihat status pengajuan sewa reklame Anda</p>
        </div>

        <div class="cek-body">
            <form method="GET" action="{{ route('sewa-reklame.cek') }}" class="cek-form">
                <div class="form-group">
                    <label class="form-label" for="nomor_tiket">Nomor Tiket</label>
                    <input type="text" class="form-input" name="nomor_tiket" id="nomor_tiket"
                           value="{{ $nomorTiket }}"
                           placeholder="Contoh: SEWA-20260316-0001" autofocus>
                </div>
                <button type="submit" class="btn-cek">
                    <i class="bi bi-search"></i> Cek Status
                </button>
            </form>

            @if($nomorTiket && $permohonan)
                @php
                    $statusLabels = [
                        'diajukan'     => 'Diajukan',
                        'perlu_revisi' => 'Perlu Revisi',
                        'diproses'     => 'Diproses',
                        'disetujui'    => 'Disetujui',
                        'ditolak'      => 'Ditolak',
                    ];
                    $statusIcons = [
                        'diajukan'     => 'clock',
                        'perlu_revisi' => 'arrow-repeat',
                        'diproses'     => 'hourglass-split',
                        'disetujui'    => 'check-circle',
                        'ditolak'      => 'x-circle',
                    ];
                @endphp
                <div class="result-card">
                    <div class="result-card-header">
                        <div class="result-icon {{ $permohonan->status }}">
                            <i class="bi bi-{{ $statusIcons[$permohonan->status] ?? 'question-circle' }}"></i>
                        </div>
                        <div class="result-info">
                            <h4>{{ $permohonan->asetReklame->nama ?? 'Aset Reklame' }}</h4>
                            <div class="ri-sub">
                                {{ $permohonan->nomor_tiket }} &bull;
                                {{ $permohonan->asetReklame->lokasi ?? '-' }}
                            </div>
                        </div>
                        <span class="result-status {{ $permohonan->status }}">
                            {{ $statusLabels[$permohonan->status] ?? ucfirst($permohonan->status) }}
                        </span>
                    </div>
                    <div class="result-card-body">
                        <div class="result-grid">
                            <div class="result-item">
                                <div class="ri-label">Pemohon</div>
                                <div class="ri-value">{{ $permohonan->nama }}</div>
                            </div>
                            <div class="result-item">
                                <div class="ri-label">Jenis Reklame</div>
                                <div class="ri-value">{{ $permohonan->jenis_reklame_dipasang }}</div>
                            </div>
                            <div class="result-item">
                                <div class="ri-label">Durasi</div>
                                <div class="ri-value">{{ $permohonan->durasi_sewa_hari }} hari</div>
                            </div>
                            <div class="result-item">
                                <div class="ri-label">Tanggal Pengajuan</div>
                                <div class="ri-value">{{ $permohonan->created_at->translatedFormat('d M Y') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="result-actions">
                        <a href="{{ route('sewa-reklame.detail', $permohonan->nomor_tiket) }}" class="btn-detail">
                            <i class="bi bi-eye"></i> Lihat Detail Lengkap
                        </a>
                    </div>
                </div>

                {{-- SKPD Info ketika disetujui --}}
                @if($permohonan->status === 'disetujui' && $permohonan->skpdReklame)
                    @php $skpd = $permohonan->skpdReklame; @endphp
                    <div class="skpd-section">
                        <div class="skpd-header">
                            <i class="bi bi-file-earmark-text"></i>
                            <div>
                                <h3>Dokumen SKPD Reklame</h3>
                                <p>Surat Ketetapan Pajak Daerah atas permohonan sewa Anda</p>
                            </div>
                        </div>
                        <div class="skpd-body">
                            <div class="skpd-grid">
                                <div class="skpd-item">
                                    <div class="sk-label">Nomor SKPD</div>
                                    <div class="sk-value">{{ $skpd->nomor_skpd }}</div>
                                </div>
                                <div class="skpd-item">
                                    <div class="sk-label">Status SKPD</div>
                                    <div class="sk-value">
                                        @if($skpd->status === 'disetujui')
                                            <span class="skpd-status-badge disetujui"><i class="bi bi-check-circle-fill"></i> Disetujui</span>
                                        @elseif($skpd->status === 'draft')
                                            <span class="skpd-status-badge draft"><i class="bi bi-hourglass-split"></i> Menunggu Verifikasi</span>
                                        @else
                                            <span class="skpd-status-badge menunggu"><i class="bi bi-clock"></i> {{ ucfirst($skpd->status) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="skpd-item">
                                    <div class="sk-label">Masa Berlaku</div>
                                    <div class="sk-value">{{ $skpd->masa_berlaku_mulai->translatedFormat('d M Y') }} — {{ $skpd->masa_berlaku_sampai->translatedFormat('d M Y') }}</div>
                                </div>
                                <div class="skpd-item">
                                    <div class="sk-label">Jumlah Pajak</div>
                                    <div class="sk-value" style="color: #2E7D32; font-size: 1rem;">Rp {{ number_format($skpd->jumlah_pajak, 0, ',', '.') }}</div>
                                </div>
                            </div>

                            @if($skpd->status === 'disetujui' && $skpd->kode_billing)
                                <div class="billing-box">
                                    <div class="bb-label">Kode Billing Pembayaran</div>
                                    <div class="bb-code" id="kodeBilling">{{ $skpd->kode_billing }}</div>
                                    <div class="bb-amount">Rp {{ number_format($skpd->jumlah_pajak, 0, ',', '.') }}</div>
                                    <button class="btn-copy-billing" onclick="copyBilling()">
                                        <i class="bi bi-clipboard"></i> <span id="copyText">Salin Kode Billing</span>
                                    </button>
                                    @if($skpd->jatuh_tempo)
                                        <div style="margin-top: 10px; font-size: 0.75rem; opacity: 0.8;">
                                            <i class="bi bi-calendar-event"></i> Jatuh Tempo: {{ $skpd->jatuh_tempo->translatedFormat('d F Y') }}
                                        </div>
                                    @endif
                                </div>

                                <div style="display: flex; gap: 10px; margin-top: 16px;">
                                    <a href="{{ \Illuminate\Support\Facades\URL::signedRoute('sewa-reklame.skpd.cetak', ['skpdId' => $skpd->id]) }}"
                                       target="_blank"
                                       style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 12px; background: #1565C0; color: #fff; border-radius: 10px; font-size: 0.82rem; font-weight: 600; text-decoration: none;">
                                        <i class="bi bi-printer"></i> Cetak SKPD
                                    </a>
                                    <a href="{{ \Illuminate\Support\Facades\URL::signedRoute('sewa-reklame.skpd.unduh', ['skpdId' => $skpd->id]) }}"
                                       style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 12px; background: #2E7D32; color: #fff; border-radius: 10px; font-size: 0.82rem; font-weight: 600; text-decoration: none;">
                                        <i class="bi bi-download"></i> Unduh PDF
                                    </a>
                                </div>
                            @elseif($skpd->status === 'draft')
                                <div class="skpd-note">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <div>SKPD Anda sedang dalam proses verifikasi. Kode billing akan tersedia setelah SKPD disetujui oleh verifikator. Silakan cek kembali secara berkala.</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @elseif($nomorTiket && !$permohonan)
                <div class="not-found">
                    <i class="bi bi-exclamation-triangle"></i>
                    <h4>Nomor Tiket Tidak Ditemukan</h4>
                    <p>Pastikan nomor tiket yang Anda masukkan benar. Format: SEWA-YYYYMMDD-XXXX</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyBilling() {
    const code = document.getElementById('kodeBilling')?.textContent?.trim();
    if (!code) return;
    navigator.clipboard.writeText(code).then(() => {
        const el = document.getElementById('copyText');
        if (el) { el.textContent = 'Berhasil Disalin!'; setTimeout(() => el.textContent = 'Salin Kode Billing', 2000); }
    });
}
</script>
@endsection
