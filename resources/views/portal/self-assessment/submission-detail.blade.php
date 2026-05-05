@extends('layouts.portal-dashboard')

@section('title', 'Detail Pengajuan MBLB - Borotax Portal')
@section('page-title', 'Detail Pengajuan MBLB')

@section('styles')
    <style>
        .mblb-detail-wrap {
            max-width: 980px;
            margin: 0 auto;
        }

        .mblb-detail-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            font-size: 0.84rem;
            font-weight: 700;
            color: var(--text-tertiary);
        }

        .mblb-detail-hero,
        .mblb-detail-card,
        .mblb-detail-note {
            border-radius: var(--radius-lg);
            background: var(--bg-card);
            border: 1px solid var(--border);
        }

        .mblb-detail-hero {
            padding: 26px 28px;
            margin-bottom: 18px;
        }

        .mblb-detail-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            margin-bottom: 14px;
        }

        .mblb-detail-status.pending { background: #fff7ed; color: #c2410c; }
        .mblb-detail-status.approved { background: #ecfdf3; color: #166534; }
        .mblb-detail-status.rejected { background: #fef2f2; color: #b91c1c; }
        .mblb-detail-status.default { background: var(--bg-surface); color: var(--text-primary); }

        .mblb-detail-hero h2 {
            margin-bottom: 8px;
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--text-primary);
        }

        .mblb-detail-hero p {
            margin: 0;
            color: var(--text-secondary);
            line-height: 1.7;
            font-size: 0.88rem;
        }

        .mblb-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 18px;
        }

        .mblb-detail-card {
            padding: 20px 22px;
        }

        .mblb-detail-card h3 {
            margin-bottom: 14px;
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--text-primary);
        }

        .mblb-detail-item + .mblb-detail-item {
            margin-top: 12px;
        }

        .mblb-detail-item .label {
            display: block;
            margin-bottom: 4px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-tertiary);
        }

        .mblb-detail-item .value {
            display: block;
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .mblb-detail-item .meta {
            display: block;
            margin-top: 4px;
            font-size: 0.78rem;
            color: var(--text-secondary);
        }

        .mblb-detail-note {
            padding: 18px 20px;
            margin-bottom: 18px;
        }

        .mblb-detail-note h3 {
            margin-bottom: 10px;
            font-size: 0.92rem;
            font-weight: 800;
        }

        .mblb-detail-note.rejected {
            border-color: #fecaca;
            background: #fef2f2;
        }

        .mblb-detail-note.rejected h3,
        .mblb-detail-note.rejected p {
            color: #b91c1c;
        }

        .mblb-detail-note.review {
            border-color: #bfdbfe;
            background: #eff6ff;
        }

        .mblb-detail-note.review h3,
        .mblb-detail-note.review p {
            color: #1d4ed8;
        }

        .mblb-detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .mblb-detail-table th,
        .mblb-detail-table td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            font-size: 0.84rem;
        }

        .mblb-detail-table th {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-tertiary);
        }

        .mblb-detail-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .mblb-detail-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            text-decoration: none;
            font-size: 0.84rem;
            font-weight: 700;
        }

        .mblb-detail-btn.primary { background: var(--primary); color: white; }
        .mblb-detail-btn.secondary { background: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border); }
        .mblb-detail-btn.warning { background: #f97316; color: white; }

        @media (max-width: 768px) {
            .mblb-detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    <div class="mblb-detail-wrap">
        <a href="{{ route('portal.mblb-submissions.index', ['status' => $submission->status]) }}" class="mblb-detail-back">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Pengajuan MBLB
        </a>

        @session('success')
            <div class="mblb-detail-note review" style="margin-bottom: 16px;">
                <h3><i class="bi bi-check-circle"></i> Berhasil</h3>
                <p>{{ session('success') }}</p>
            </div>
        @endsession

        <div class="mblb-detail-hero">
            <span class="mblb-detail-status {{ $submission->status_badge_class }}">
                <i class="bi bi-{{ $submission->isPending() ? 'clock-history' : ($submission->isApproved() ? 'check-circle' : 'x-circle') }}"></i>
                {{ $submission->status_label }}
            </span>
            <h2>{{ $submission->taxObject?->nama_objek_pajak ?? 'Pengajuan MBLB' }}</h2>
            <p>
                Detail ini dapat dipakai untuk memantau submission MBLB portal pada status menunggu verifikasi,
                disetujui, maupun ditolak. Saat status ditolak, perbaiki data sesuai catatan verifikator lalu kirim ulang.
            </p>
        </div>

        @if($submission->rejection_reason)
            <div class="mblb-detail-note rejected">
                <h3><i class="bi bi-exclamation-triangle"></i> Catatan Penolakan Verifikator</h3>
                <p>{{ $submission->rejection_reason }}</p>
            </div>
        @endif

        @if($submission->review_notes)
            <div class="mblb-detail-note review">
                <h3><i class="bi bi-chat-left-text"></i> Catatan Verifikator</h3>
                <p>{{ $submission->review_notes }}</p>
            </div>
        @endif

        <div class="mblb-detail-grid">
            <section class="mblb-detail-card">
                <h3>Informasi Pengajuan</h3>
                <div class="mblb-detail-item">
                    <span class="label">Jenis Pajak</span>
                    <span class="value">{{ $submission->jenisPajak?->nama ?? 'MBLB' }}</span>
                </div>
                <div class="mblb-detail-item">
                    <span class="label">Masa Pajak</span>
                    <span class="value">{{ $submission->masa_pajak_label }}</span>
                </div>
                <div class="mblb-detail-item">
                    <span class="label">Dikirim Pada</span>
                    <span class="value">{{ $submission->created_at?->format('d/m/Y H:i') ?? '-' }}</span>
                    @if($submission->processed_at)
                        <span class="meta">Diproses {{ $submission->processed_at->format('d/m/Y H:i') }}</span>
                    @endif
                </div>
                <div class="mblb-detail-item">
                    <span class="label">Verifikator</span>
                    <span class="value">{{ $submission->reviewer?->nama_lengkap ?? $submission->reviewer?->name ?? '-' }}</span>
                </div>
                <div class="mblb-detail-item">
                    <span class="label">Catatan Pemohon</span>
                    <span class="value">{{ $submission->notes ?: '-' }}</span>
                </div>
            </section>

            <section class="mblb-detail-card">
                <h3>Objek Pajak & Tagihan</h3>
                <div class="mblb-detail-item">
                    <span class="label">Objek Pajak</span>
                    <span class="value">{{ $submission->taxObject?->nama_objek_pajak ?? '-' }}</span>
                    <span class="meta">NPWPD {{ $submission->taxObject?->npwpd ?? '-' }}</span>
                </div>
                <div class="mblb-detail-item">
                    <span class="label">Instansi / Lembaga</span>
                    <span class="value">{{ $submission->instansi_nama ?: '-' }}</span>
                    @if($submission->instansi_kategori)
                        <span class="meta">{{ $submission->instansi_kategori->getLabel() }}</span>
                    @endif
                </div>
                <div class="mblb-detail-item">
                    <span class="label">Estimasi Tagihan</span>
                    <span class="value">Rp {{ number_format((float) $submission->total_tagihan, 0, ',', '.') }}</span>
                    <span class="meta">DPP Rp {{ number_format((float) $submission->total_dpp, 0, ',', '.') }} &bull; Pokok Rp {{ number_format((float) $submission->pokok_pajak, 0, ',', '.') }} &bull; Opsen Rp {{ number_format((float) $submission->opsen, 0, ',', '.') }}</span>
                </div>
                <div class="mblb-detail-item">
                    <span class="label">Lampiran</span>
                    @if($submission->attachment_url)
                        <a href="{{ route('portal.mblb-submissions.attachment', $submission->id) }}" target="_blank" class="mblb-detail-btn secondary" style="display:inline-flex; margin-top: 4px;">
                            <i class="bi bi-paperclip"></i> Buka Lampiran
                        </a>
                    @else
                        <span class="value">-</span>
                    @endif
                </div>
            </section>
        </div>

        <section class="mblb-detail-card" style="margin-bottom: 18px;">
            <h3>Detail Mineral</h3>
            <div style="overflow-x: auto;">
                <table class="mblb-detail-table">
                    <thead>
                        <tr>
                            <th>Jenis Material</th>
                            <th>Volume</th>
                            <th>Harga Patokan</th>
                            <th>Subtotal DPP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($submission->detail_items ?? []) as $item)
                            @php
                                $volume = (float) ($item['volume'] ?? 0);
                                $hargaPatokan = (float) ($item['harga_patokan'] ?? 0);
                            @endphp
                            @if($volume > 0)
                                <tr>
                                    <td>{{ $item['jenis_mblb'] ?? '-' }}</td>
                                    <td>{{ number_format($volume, 2, ',', '.') }} m3</td>
                                    <td>Rp {{ number_format($hargaPatokan, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($volume * $hargaPatokan, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mblb-detail-actions">
            <a href="{{ route('portal.mblb-submissions.index', ['status' => $submission->status]) }}" class="mblb-detail-btn secondary">
                <i class="bi bi-list-ul"></i> Kembali ke Daftar
            </a>
            @if($submission->canBeRevised())
                <a href="{{ route('portal.mblb-submissions.edit', $submission->id) }}" class="mblb-detail-btn warning">
                    <i class="bi bi-arrow-repeat"></i> Perbaiki Pengajuan
                </a>
            @elseif($submission->isApproved() && $submission->approvedTax)
                <a href="{{ route('portal.billing.document.show', $submission->approvedTax->id) }}" target="_blank" class="mblb-detail-btn primary">
                    <i class="bi bi-receipt"></i> Lihat Billing Terbit
                </a>
                <a href="{{ route('portal.history') }}" class="mblb-detail-btn secondary">
                    <i class="bi bi-clock-history"></i> Riwayat Transaksi
                </a>
            @else
                <a href="{{ route('portal.self-assessment.index') }}" class="mblb-detail-btn primary">
                    <i class="bi bi-file-earmark-plus"></i> Buat Pengajuan Baru
                </a>
            @endif
        </div>
    </div>
@endsection