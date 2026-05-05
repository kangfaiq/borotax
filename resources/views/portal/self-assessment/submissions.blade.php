@extends('layouts.portal-dashboard')

@section('title', 'Pengajuan MBLB - Borotax Portal')
@section('page-title', 'Pengajuan MBLB')

@section('styles')
    <style>
        .mblb-submission-wrap {
            max-width: 980px;
            margin: 0 auto;
        }

        .mblb-submission-hero {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 26px 28px;
            margin-bottom: 22px;
            border-radius: var(--radius-lg);
            border: 1px solid #bfdbfe;
            background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);
        }

        .mblb-submission-hero-icon {
            width: 52px;
            height: 52px;
            flex-shrink: 0;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
            font-size: 1.4rem;
        }

        .mblb-submission-hero h2 {
            margin-bottom: 6px;
            font-size: 1.18rem;
            font-weight: 800;
            color: #1e3a8a;
        }

        .mblb-submission-hero p {
            margin: 0;
            line-height: 1.7;
            color: #1d4ed8;
            font-size: 0.88rem;
        }

        .mblb-submission-list {
            display: grid;
            gap: 16px;
        }

        .mblb-submission-card {
            padding: 22px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            background: var(--bg-card);
            box-shadow: var(--shadow-sm);
        }

        .mblb-submission-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }

        .mblb-submission-name {
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .mblb-submission-meta {
            font-size: 0.8rem;
            color: var(--text-tertiary);
        }

        .mblb-submission-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            background: #fff7ed;
            color: #c2410c;
            font-size: 0.75rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .mblb-submission-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .mblb-submission-stat {
            padding: 14px 16px;
            border-radius: var(--radius-md);
            background: var(--bg-surface);
            border: 1px solid var(--border);
        }

        .mblb-submission-stat .label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--text-tertiary);
        }

        .mblb-submission-stat .value {
            display: block;
            font-size: 0.94rem;
            font-weight: 800;
            color: var(--text-primary);
        }

        .mblb-submission-stat .meta {
            display: block;
            margin-top: 4px;
            font-size: 0.76rem;
            color: var(--text-secondary);
        }

        .mblb-submission-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .mblb-submission-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 15px;
            border-radius: var(--radius-md);
            text-decoration: none;
            font-size: 0.84rem;
            font-weight: 700;
        }

        .mblb-submission-btn.primary {
            background: var(--primary);
            color: white;
        }

        .mblb-submission-btn.secondary {
            background: var(--bg-card);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .mblb-submission-empty {
            padding: 34px 28px;
            text-align: center;
            border-radius: var(--radius-lg);
            border: 1px dashed var(--border);
            background: var(--bg-card);
        }

        .mblb-submission-empty i {
            display: block;
            margin-bottom: 10px;
            font-size: 2rem;
            color: var(--text-tertiary);
        }

        .mblb-submission-empty h3 {
            margin-bottom: 8px;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary);
        }

        .mblb-submission-empty p {
            margin: 0 auto 14px;
            max-width: 540px;
            font-size: 0.84rem;
            line-height: 1.7;
            color: var(--text-secondary);
        }

        @media (max-width: 768px) {
            .mblb-submission-hero {
                padding: 22px 20px;
            }

            .mblb-submission-top {
                flex-direction: column;
            }

            .mblb-submission-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    <div class="mblb-submission-wrap">
        <div class="mblb-submission-hero">
            <div class="mblb-submission-hero-icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <h2>Pengajuan MBLB Portal</h2>
                <p>
                    Pantau seluruh pengajuan billing MBLB portal Anda. Submission yang disetujui akan berpindah ke alur billing,
                    sementara submission yang ditolak dapat dibuka detailnya untuk melihat catatan verifikator dan dikirim ulang.
                </p>
            </div>
        </div>

        <div class="mblb-submission-actions" style="margin-bottom: 18px;">
            <a href="{{ route('portal.mblb-submissions.index', ['status' => 'pending']) }}"
               class="mblb-submission-btn {{ $activeStatus === 'pending' ? 'primary' : 'secondary' }}">
                <i class="bi bi-hourglass-split"></i> Menunggu Verifikasi ({{ $statusCounts['pending'] ?? 0 }})
            </a>
            <a href="{{ route('portal.mblb-submissions.index', ['status' => 'approved']) }}"
               class="mblb-submission-btn {{ $activeStatus === 'approved' ? 'primary' : 'secondary' }}">
                <i class="bi bi-check-circle"></i> Disetujui ({{ $statusCounts['approved'] ?? 0 }})
            </a>
            <a href="{{ route('portal.mblb-submissions.index', ['status' => 'rejected']) }}"
               class="mblb-submission-btn {{ $activeStatus === 'rejected' ? 'primary' : 'secondary' }}">
                <i class="bi bi-x-circle"></i> Ditolak ({{ $statusCounts['rejected'] ?? 0 }})
            </a>
        </div>

        <div class="mblb-submission-list">
        @forelse($submissions as $submission)
                <article class="mblb-submission-card">
                    <div class="mblb-submission-top">
                        <div>
                            <div class="mblb-submission-name">{{ $submission->taxObject?->nama_objek_pajak ?? '-' }}</div>
                            <div class="mblb-submission-meta">
                                Dikirim {{ $submission->created_at?->format('d/m/Y H:i') }}
                                @if(filled($submission->instansi_nama))
                                    &bull; {{ $submission->instansi_nama }}
                                @endif
                            </div>
                        </div>
                        <span class="mblb-submission-badge {{ $submission->status_badge_class }}">
                            <i class="bi bi-{{ $submission->isPending() ? 'clock-history' : ($submission->isApproved() ? 'check-circle' : 'x-circle') }}"></i>
                            {{ $submission->status_label }}
                        </span>
                    </div>

                    <div class="mblb-submission-grid">
                        <div class="mblb-submission-stat">
                            <span class="label">Masa Pajak</span>
                            <span class="value">{{ $submission->masa_pajak_label }}</span>
                            <span class="meta">Tarif {{ number_format((float) $submission->tarif_persen, 0) }}% + Opsen {{ number_format((float) $submission->opsen_persen, 0) }}%</span>
                        </div>
                        <div class="mblb-submission-stat">
                            <span class="label">Estimasi Tagihan</span>
                            <span class="value">Rp {{ number_format((float) $submission->total_tagihan, 0, ',', '.') }}</span>
                            <span class="meta">DPP Rp {{ number_format((float) $submission->total_dpp, 0, ',', '.') }}</span>
                        </div>
                        <div class="mblb-submission-stat">
                            <span class="label">Objek Pajak</span>
                            <span class="value">{{ $submission->taxObject?->npwpd ?? '-' }}</span>
                            <span class="meta">{{ $submission->jenisPajak?->nama ?? 'MBLB' }}</span>
                        </div>
                    </div>

                    <div class="mblb-submission-actions">
                        <a href="{{ route('portal.mblb-submissions.show', $submission->id) }}" class="mblb-submission-btn primary">
                            <i class="bi bi-eye"></i> Lihat Detail Pengajuan
                        </a>
                        @if($submission->canBeRevised())
                            <a href="{{ route('portal.mblb-submissions.edit', $submission->id) }}" class="mblb-submission-btn secondary">
                                <i class="bi bi-arrow-repeat"></i> Perbaiki Pengajuan
                            </a>
                        @elseif($submission->isApproved() && $submission->approvedTax)
                            <a href="{{ route('portal.billing.document.show', $submission->approvedTax->id) }}" target="_blank" class="mblb-submission-btn secondary">
                                <i class="bi bi-receipt"></i> Lihat Billing
                            </a>
                        @endif
                        <a href="{{ route('portal.self-assessment.index') }}" class="mblb-submission-btn secondary">
                            <i class="bi bi-plus-circle"></i> Buat Pengajuan Baru
                        </a>
                    </div>
                </article>
        @empty
            <div class="mblb-submission-empty">
                <i class="bi bi-inbox"></i>
                <h3>Belum ada pengajuan MBLB pada status ini</h3>
                <p>
                    Anda dapat membuat pengajuan MBLB baru dari menu Self Assessment.
                    Submission yang disetujui akan berpindah ke Cek Billing dan Riwayat Transaksi.
                </p>
                <a href="{{ route('portal.self-assessment.index') }}" class="mblb-submission-btn primary">
                    <i class="bi bi-file-earmark-plus"></i> Buat Pengajuan MBLB
                </a>
            </div>
        @endforelse
        </div>
    </div>
@endsection