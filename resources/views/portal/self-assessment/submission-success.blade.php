@extends('layouts.portal-dashboard')

@section('title', 'Pengajuan MBLB Berhasil - Borotax Portal')
@section('page-title', 'Pengajuan MBLB')

@section('styles')
    <style>
        .submission-success-wrap {
            max-width: 860px;
            margin: 0 auto;
        }

        .submission-hero {
            background: linear-gradient(135deg, #ecfdf3 0%, #f0fdf4 100%);
            border: 1px solid #bbf7d0;
            border-radius: var(--radius-lg);
            padding: 28px;
            margin-bottom: 20px;
        }

        .submission-hero h2 {
            font-size: 1.25rem;
            font-weight: 800;
            color: #14532d;
            margin-bottom: 8px;
        }

        .submission-hero p {
            color: #166534;
            font-size: 0.9rem;
            line-height: 1.7;
        }

        .submission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .submission-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 18px;
        }

        .submission-card .label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-tertiary);
            margin-bottom: 6px;
        }

        .submission-card .value {
            color: var(--text-primary);
            font-weight: 700;
            font-size: 0.96rem;
        }

        .submission-card .meta {
            color: var(--text-secondary);
            font-size: 0.8rem;
            margin-top: 4px;
        }

        .submission-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .submission-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 0.88rem;
            text-decoration: none;
        }

        .submission-btn.primary {
            background: var(--primary);
            color: white;
        }

        .submission-btn.secondary {
            background: var(--bg-card);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }
    </style>
@endsection

@section('content')
    <div class="submission-success-wrap">
        <div class="submission-hero">
            <h2>Pengajuan billing MBLB berhasil dikirim</h2>
            <p>
                Pengajuan Anda belum menerbitkan kode billing. Admin atau verifikator akan memeriksa detail mineral dan
                lampiran pendukung terlebih dahulu. Setelah disetujui, kode billing akan dikirim ke akun Anda.
            </p>
        </div>

        <div class="submission-grid">
            <div class="submission-card">
                <div class="label">Status</div>
                <div class="value">Menunggu Verifikasi</div>
                <div class="meta">Dikirim {{ $submission->created_at?->format('d/m/Y H:i') }}</div>
            </div>
            <div class="submission-card">
                <div class="label">Objek Pajak</div>
                <div class="value">{{ $submission->taxObject?->nama_objek_pajak ?? '-' }}</div>
                <div class="meta">NPWPD {{ $submission->taxObject?->npwpd ?? '-' }}</div>
            </div>
            <div class="submission-card">
                <div class="label">Masa Pajak</div>
                <div class="value">{{ $submission->masa_pajak_label }}</div>
                <div class="meta">Tarif {{ number_format((float) $submission->tarif_persen, 0) }}% + Opsen {{ number_format((float) $submission->opsen_persen, 0) }}%</div>
            </div>
            <div class="submission-card">
                <div class="label">Estimasi Tagihan</div>
                <div class="value">Rp {{ number_format($submission->total_tagihan, 0, ',', '.') }}</div>
                <div class="meta">DPP Rp {{ number_format((float) $submission->total_dpp, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="submission-actions">
            <a href="{{ route('portal.self-assessment.index') }}" class="submission-btn primary">
                <i class="bi bi-grid"></i> Kembali ke Self Assessment
            </a>
            <a href="{{ route('portal.mblb-submissions.show', $submission->id) }}" class="submission-btn secondary">
                <i class="bi bi-eye"></i> Detail Pengajuan
            </a>
            <a href="{{ route('portal.mblb-submissions.index') }}" class="submission-btn secondary">
                <i class="bi bi-hourglass-split"></i> Daftar Pengajuan MBLB
            </a>
            <a href="{{ route('portal.dashboard') }}" class="submission-btn secondary">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </div>
    </div>
@endsection