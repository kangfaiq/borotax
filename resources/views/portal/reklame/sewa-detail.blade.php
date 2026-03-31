@extends('layouts.portal-guest')

@section('title', 'Detail Permohonan Sewa - Borotax Portal')

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

    /* Detail card */
    .detail-card {
        background: var(--bg-card);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        margin-bottom: 20px;
    }

    .detail-header {
        padding: 24px 28px;
        display: flex;
        align-items: center;
        gap: 16px;
        border-bottom: 1px solid var(--border);
    }

    .detail-header .dh-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .dh-icon.diajukan      { background: #E3F2FD; color: #1565C0; }
    .dh-icon.perlu_revisi   { background: #E3F2FD; color: #0277BD; }
    .dh-icon.diproses       { background: #FFF8E1; color: #F57F17; }
    .dh-icon.disetujui      { background: #E8F5E9; color: #2E7D32; }
    .dh-icon.ditolak        { background: #FFEBEE; color: #C62828; }

    .detail-header h2 {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .detail-header .dh-sub {
        font-size: 0.82rem;
        color: var(--text-tertiary);
    }

    .detail-status {
        margin-left: auto;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 6px 16px;
        border-radius: var(--radius-full);
    }

    .detail-status.diajukan      { background: #E3F2FD; color: #1565C0; }
    .detail-status.perlu_revisi   { background: #E3F2FD; color: #0277BD; }
    .detail-status.diproses       { background: #FFF8E1; color: #F57F17; }
    .detail-status.disetujui      { background: #E8F5E9; color: #2E7D32; }
    .detail-status.ditolak        { background: #FFEBEE; color: #C62828; }

    /* Section */
    .detail-section {
        padding: 24px 28px;
        border-bottom: 1px solid var(--border);
    }

    .detail-section:last-child { border-bottom: none; }

    .detail-section-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 16px;
    }

    .detail-item {
        font-size: 0.85rem;
    }

    .detail-item .di-label {
        color: var(--text-tertiary);
        font-size: 0.78rem;
        margin-bottom: 3px;
    }

    .detail-item .di-value {
        font-weight: 600;
        color: var(--text-primary);
    }

    /* SKPD link card */
    .skpd-link-card {
        background: #E8F5E9;
        border: 1px solid #A5D6A7;
        border-radius: var(--radius-lg);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
        color: inherit;
        transition: all var(--transition);
    }

    .skpd-link-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }

    .skpd-link-card .slc-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-md);
        background: rgba(46, 125, 50, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: #2E7D32;
        flex-shrink: 0;
    }

    .skpd-link-card .slc-title {
        font-size: 0.88rem;
        font-weight: 700;
        color: #2E7D32;
        margin-bottom: 2px;
    }

    .skpd-link-card .slc-sub {
        font-size: 0.78rem;
        color: #4CAF50;
    }

    .skpd-link-card .slc-arrow {
        margin-left: auto;
        color: #2E7D32;
        font-size: 1.2rem;
    }

    /* Rejection info */
    .rejection-info {
        background: #FFEBEE;
        border: 1px solid #EF9A9A;
        border-radius: var(--radius-lg);
        padding: 16px 20px;
    }

    .rejection-info h4 {
        font-size: 0.82rem;
        font-weight: 700;
        color: #C62828;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .rejection-info p {
        font-size: 0.85rem;
        color: #B71C1C;
    }

    /* Revision info */
    .revision-info {
        background: #E3F2FD;
        border: 1px solid #90CAF9;
        border-radius: var(--radius-lg);
        padding: 16px 20px;
    }

    .revision-info h4 {
        font-size: 0.82rem;
        font-weight: 700;
        color: #0277BD;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .revision-info p {
        font-size: 0.85rem;
        color: #01579B;
        margin-bottom: 12px;
    }

    .btn-revisi {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        background: linear-gradient(140deg, #29B6F6 0%, #0277BD 100%);
        color: #fff;
        border-radius: var(--radius-full);
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
        transition: all var(--transition);
    }

    .btn-revisi:hover { box-shadow: var(--shadow-lg); transform: translateY(-1px); color: #fff; }

    /* Document list */
    .doc-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .doc-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        background: var(--bg-surface-variant);
        border-radius: var(--radius-md);
        font-size: 0.82rem;
    }

    .doc-item i {
        font-size: 1rem;
        color: var(--primary);
    }

    .doc-item .doc-name {
        font-weight: 600;
        color: var(--text-primary);
    }

    .doc-item .doc-status {
        margin-left: auto;
        font-size: 0.75rem;
        color: var(--text-tertiary);
    }

    /* Timeline */
    .timeline {
        padding-left: 20px;
        border-left: 2px solid var(--border);
    }

    .timeline-item {
        position: relative;
        padding-bottom: 20px;
        padding-left: 20px;
    }

    .timeline-item:last-child { padding-bottom: 0; }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -7px;
        top: 4px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--border);
        border: 2px solid var(--bg-card);
    }

    .timeline-item.active::before { background: #1565C0; }

    .timeline-item .ti-date {
        font-size: 0.72rem;
        color: var(--text-tertiary);
        margin-bottom: 2px;
    }

    .timeline-item .ti-text {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    @media (max-width: 768px) {
        .detail-grid { grid-template-columns: 1fr; }
        .detail-header { flex-wrap: wrap; }
        .detail-status { margin-left: 0; }
        .detail-section { padding: 18px 20px; }
    }
</style>
@endsection

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 100px 20px 40px;">
    <a href="{{ route('sewa-reklame.cek', ['nomor_tiket' => $permohonan->nomor_tiket]) }}" class="obj-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Cek Status
    </a>

    @php
        $statusLabels = [
            'diajukan' => 'Diajukan',
            'perlu_revisi' => 'Perlu Revisi',
            'diproses' => 'Sedang Diproses',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
        ];
        $statusIcons = [
            'diajukan' => 'clock',
            'perlu_revisi' => 'arrow-repeat',
            'diproses' => 'hourglass-split',
            'disetujui' => 'check-circle',
            'ditolak' => 'x-circle',
        ];
    @endphp

    {{-- Status Header --}}
    <div class="detail-card">
        <div class="detail-header">
            <div class="dh-icon {{ $permohonan->status }}">
                <i class="bi bi-{{ $statusIcons[$permohonan->status] ?? 'question-circle' }}"></i>
            </div>
            <div>
                <h2>Permohonan Sewa Reklame</h2>
                <div class="dh-sub">
                    <strong>{{ $permohonan->nomor_tiket }}</strong> &bull;
                    Diajukan {{ $permohonan->created_at->translatedFormat('d F Y, H:i') }}
                </div>
            </div>
            <span class="detail-status {{ $permohonan->status }}">
                {{ $statusLabels[$permohonan->status] ?? ucfirst($permohonan->status) }}
            </span>
        </div>

        {{-- Info Aset --}}
        <div class="detail-section">
            <div class="detail-section-title"><i class="bi bi-signpost-2"></i> Informasi Aset</div>
            @if($permohonan->asetReklame)
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="di-label">Nama Aset</div>
                        <div class="di-value">{{ $permohonan->asetReklame->nama }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="di-label">Kode Aset</div>
                        <div class="di-value">{{ $permohonan->asetReklame->kode_aset }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="di-label">Jenis</div>
                        <div class="di-value">{{ ucfirst(str_replace('_', ' ', $permohonan->asetReklame->jenis)) }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="di-label">Lokasi</div>
                        <div class="di-value">{{ $permohonan->asetReklame->lokasi }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="di-label">Ukuran</div>
                        <div class="di-value">{{ $permohonan->asetReklame->panjang }}m × {{ $permohonan->asetReklame->lebar }}m ({{ $permohonan->asetReklame->luas_m2 }} m²)</div>
                    </div>
                </div>
            @else
                <p style="font-size: 0.85rem; color: var(--text-tertiary);">Data aset tidak tersedia.</p>
            @endif
        </div>

        {{-- Info Permohonan --}}
        <div class="detail-section">
            <div class="detail-section-title"><i class="bi bi-file-earmark-text"></i> Detail Permohonan</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="di-label">Jenis Reklame</div>
                    <div class="di-value">{{ $permohonan->jenis_reklame_dipasang }}</div>
                </div>
                <div class="detail-item">
                    <div class="di-label">Durasi Sewa</div>
                    <div class="di-value">{{ $permohonan->durasi_sewa_hari }} hari</div>
                </div>
                <div class="detail-item">
                    <div class="di-label">Tanggal Mulai Diinginkan</div>
                    <div class="di-value">{{ \Carbon\Carbon::parse($permohonan->tanggal_mulai_diinginkan)->translatedFormat('d F Y') }}</div>
                </div>
                @if($permohonan->nama_usaha)
                    <div class="detail-item">
                        <div class="di-label">Nama Usaha</div>
                        <div class="di-value">{{ $permohonan->nama_usaha }}</div>
                    </div>
                @endif
                <div class="detail-item">
                    <div class="di-label">No. Registrasi Izin DPMPTSP</div>
                    <div class="di-value">{{ $permohonan->nomor_registrasi_izin }}</div>
                </div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="detail-section">
            <div class="detail-section-title"><i class="bi bi-clock-history"></i> Riwayat Status</div>
            <div class="timeline">
                <div class="timeline-item active">
                    <div class="ti-date">{{ $permohonan->created_at->translatedFormat('d M Y, H:i') }}</div>
                    <div class="ti-text">Permohonan diajukan</div>
                </div>
                @if(in_array($permohonan->status, ['diproses', 'disetujui', 'ditolak']))
                    <div class="timeline-item {{ in_array($permohonan->status, ['disetujui', 'ditolak']) ? '' : 'active' }}">
                        <div class="ti-date">{{ $permohonan->updated_at->translatedFormat('d M Y, H:i') }}</div>
                        <div class="ti-text">Permohonan sedang diproses petugas</div>
                    </div>
                @endif
                @if($permohonan->status === 'perlu_revisi')
                    <div class="timeline-item active">
                        <div class="ti-date">{{ $permohonan->updated_at->translatedFormat('d M Y, H:i') }}</div>
                        <div class="ti-text">Permohonan perlu direvisi pemohon</div>
                    </div>
                @endif
                @if($permohonan->status === 'disetujui')
                    <div class="timeline-item active">
                        <div class="ti-date">{{ $permohonan->updated_at->translatedFormat('d M Y, H:i') }}</div>
                        <div class="ti-text">Permohonan disetujui — SKPD telah diterbitkan</div>
                    </div>
                @endif
                @if($permohonan->status === 'ditolak')
                    <div class="timeline-item active">
                        <div class="ti-date">{{ $permohonan->updated_at->translatedFormat('d M Y, H:i') }}</div>
                        <div class="ti-text">Permohonan ditolak</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Revision Info --}}
        @if($permohonan->status === 'perlu_revisi' && $permohonan->catatan_petugas)
            <div class="detail-section">
                <div class="revision-info">
                    <h4><i class="bi bi-arrow-repeat"></i> Perlu Revisi dari Petugas</h4>
                    <p>{{ $permohonan->catatan_petugas }}</p>
                    <a href="{{ route('sewa-reklame.edit', $permohonan->nomor_tiket) }}" class="btn-revisi">
                        <i class="bi bi-pencil-square"></i> Edit & Kirim Ulang
                    </a>
                </div>
            </div>
        @endif

        {{-- Rejection --}}
        @if($permohonan->status === 'ditolak' && $permohonan->catatan_petugas)
            <div class="detail-section">
                <div class="rejection-info">
                    <h4><i class="bi bi-exclamation-triangle-fill"></i> Alasan Penolakan</h4>
                    <p>{{ $permohonan->catatan_petugas }}</p>
                </div>
            </div>
        @endif

        {{-- Dokumen --}}
        <div class="detail-section">
            <div class="detail-section-title"><i class="bi bi-paperclip"></i> Dokumen</div>
            <div class="doc-list">
                <div class="doc-item">
                    <i class="bi bi-file-earmark-person"></i>
                    <span class="doc-name">KTP</span>
                    <span class="doc-status">{{ $permohonan->file_ktp ? 'Terupload' : 'Belum ada' }}</span>
                </div>
                <div class="doc-item">
                    <i class="bi bi-file-earmark-text"></i>
                    <span class="doc-name">NPWP</span>
                    <span class="doc-status">{{ $permohonan->file_npwp ? 'Terupload' : 'Tidak ada (opsional)' }}</span>
                </div>
                <div class="doc-item">
                    <i class="bi bi-file-earmark-image"></i>
                    <span class="doc-name">Desain Reklame</span>
                    <span class="doc-status">{{ $permohonan->file_desain_reklame ? 'Terupload' : 'Belum ada' }}</span>
                </div>
            </div>
        </div>

        {{-- SKPD Link --}}
        @if($permohonan->skpdReklame && $permohonan->status === 'disetujui')
            @php $skpd = $permohonan->skpdReklame; @endphp
            <div class="detail-section">
                <div class="detail-section-title"><i class="bi bi-file-earmark-check"></i> Dokumen SKPD</div>
                <div class="skpd-link-card" style="cursor: default; flex-direction: column; align-items: stretch; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div class="slc-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                        <div style="flex: 1;">
                            <div class="slc-title">{{ $skpd->nomor_skpd }}</div>
                            <div class="slc-sub">
                                Masa Berlaku: {{ $skpd->masa_berlaku_mulai->translatedFormat('d M Y') }} — {{ $skpd->masa_berlaku_sampai->translatedFormat('d M Y') }}
                            </div>
                        </div>
                        @if($skpd->status === 'disetujui')
                            <span style="background: #C8E6C9; color: #2E7D32; font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 50px;">Disetujui</span>
                        @elseif($skpd->status === 'draft')
                            <span style="background: #FFF3E0; color: #E65100; font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 50px;">Menunggu Verifikasi</span>
                        @endif
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <div style="font-size: 0.72rem; color: #4CAF50;">Jumlah Pajak</div>
                            <div style="font-size: 1rem; font-weight: 700; color: #2E7D32;">Rp {{ number_format($skpd->jumlah_pajak, 0, ',', '.') }}</div>
                        </div>
                        @if($skpd->jatuh_tempo)
                            <div>
                                <div style="font-size: 0.72rem; color: #4CAF50;">Jatuh Tempo</div>
                                <div style="font-size: 0.88rem; font-weight: 600; color: #2E7D32;">{{ $skpd->jatuh_tempo->translatedFormat('d F Y') }}</div>
                            </div>
                        @endif
                    </div>

                    @if($skpd->status === 'disetujui' && $skpd->kode_billing)
                        <div style="background: linear-gradient(140deg, #1B5E20 0%, #2E7D32 100%); border-radius: 12px; padding: 18px; color: #fff; text-align: center;">
                            <div style="font-size: 0.75rem; opacity: 0.8;">Kode Billing Pembayaran</div>
                            <div id="kodeBillingDetail" style="font-size: 1.4rem; font-weight: 800; letter-spacing: 2px; font-family: 'Courier New', monospace; margin: 4px 0;">{{ $skpd->kode_billing }}</div>
                            <div style="font-size: 1rem; font-weight: 700; margin-bottom: 10px;">Rp {{ number_format($skpd->jumlah_pajak, 0, ',', '.') }}</div>
                            <button onclick="copyBillingDetail()" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 20px; background: rgba(255,255,255,0.2); color: #fff; border: 1px solid rgba(255,255,255,0.3); border-radius: 50px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                <i class="bi bi-clipboard"></i> <span id="copyTextDetail">Salin Kode Billing</span>
                            </button>
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 4px;">
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
                        <div style="padding: 14px; background: #F1F8E9; border: 1px solid #DCEDC8; border-radius: 8px; font-size: 0.8rem; color: #33691E; display: flex; align-items: flex-start; gap: 8px;">
                            <i class="bi bi-info-circle-fill" style="flex-shrink: 0; margin-top: 1px;"></i>
                            <div>SKPD sedang dalam proses verifikasi. Kode billing akan tersedia setelah disetujui verifikator. Silakan cek kembali secara berkala.</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyBillingDetail() {
    const code = document.getElementById('kodeBillingDetail')?.textContent?.trim();
    if (!code) return;
    navigator.clipboard.writeText(code).then(() => {
        const el = document.getElementById('copyTextDetail');
        if (el) { el.textContent = 'Berhasil Disalin!'; setTimeout(() => el.textContent = 'Salin Kode Billing', 2000); }
    });
}
</script>
@endsection
