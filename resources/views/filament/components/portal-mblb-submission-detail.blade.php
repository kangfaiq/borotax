@php
    $detailItems = collect($record->detail_items ?? []);
    $attachmentUrl = $record->attachment_path
        ? route('portal.mblb-submissions.attachment', $record)
        : null;
    $isPdf = str_ends_with(strtolower($record->attachment_path ?? ''), '.pdf');
@endphp

<style>
    .portal-mblb-detail {
        --portal-mblb-detail-border: #e2e8f0;
        --portal-mblb-detail-bg-muted: #f8fafc;
        --portal-mblb-detail-heading: #0f172a;
        --portal-mblb-detail-text: #334155;
        --portal-mblb-detail-text-muted: #64748b;
        --portal-mblb-detail-link: #2563eb;
    }

    .dark .portal-mblb-detail {
        --portal-mblb-detail-border: rgba(148, 163, 184, 0.28);
        --portal-mblb-detail-bg-muted: rgba(15, 23, 42, 0.72);
        --portal-mblb-detail-heading: #f8fafc;
        --portal-mblb-detail-text: #e2e8f0;
        --portal-mblb-detail-text-muted: #cbd5e1;
        --portal-mblb-detail-link: #93c5fd;
    }
</style>

<div class="portal-mblb-detail" style="display:grid; gap:16px; max-width:860px;">
    <div>
        <div style="font-weight:700; font-size:1rem; color:var(--portal-mblb-detail-heading); margin-bottom:8px;">Ringkasan Pengajuan</div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
            <div style="padding:12px 14px; border:1px solid var(--portal-mblb-detail-border); border-radius:10px;">
                <div style="font-size:12px; color:var(--portal-mblb-detail-text-muted); text-transform:uppercase; letter-spacing:.04em;">Wajib Pajak</div>
                <div style="font-weight:600; color:var(--portal-mblb-detail-heading);">{{ $record->user?->nama_lengkap ?? '-' }}</div>
                <div style="font-size:13px; color:var(--portal-mblb-detail-text);">{{ $record->user?->nik ?? '-' }}</div>
            </div>
            <div style="padding:12px 14px; border:1px solid var(--portal-mblb-detail-border); border-radius:10px;">
                <div style="font-size:12px; color:var(--portal-mblb-detail-text-muted); text-transform:uppercase; letter-spacing:.04em;">Objek Pajak</div>
                <div style="font-weight:600; color:var(--portal-mblb-detail-heading);">{{ $record->taxObject?->nama_objek_pajak ?? '-' }}</div>
                <div style="font-size:13px; color:var(--portal-mblb-detail-text);">NPWPD {{ $record->taxObject?->npwpd ?? '-' }}</div>
            </div>
            <div style="padding:12px 14px; border:1px solid var(--portal-mblb-detail-border); border-radius:10px;">
                <div style="font-size:12px; color:var(--portal-mblb-detail-text-muted); text-transform:uppercase; letter-spacing:.04em;">Masa Pajak</div>
                <div style="font-weight:600; color:var(--portal-mblb-detail-heading);">{{ $record->masa_pajak_label }}</div>
                <div style="font-size:13px; color:var(--portal-mblb-detail-text);">Tarif {{ number_format((float) $record->tarif_persen, 0) }}% + Opsen {{ number_format((float) $record->opsen_persen, 0) }}%</div>
            </div>
            <div style="padding:12px 14px; border:1px solid var(--portal-mblb-detail-border); border-radius:10px;">
                <div style="font-size:12px; color:var(--portal-mblb-detail-text-muted); text-transform:uppercase; letter-spacing:.04em;">Estimasi Tagihan</div>
                <div style="font-weight:700; color:var(--portal-mblb-detail-heading);">Rp {{ number_format($record->total_tagihan, 0, ',', '.') }}</div>
                <div style="font-size:13px; color:var(--portal-mblb-detail-text);">DPP Rp {{ number_format((float) $record->total_dpp, 0, ',', '.') }}</div>
            </div>
            <div style="padding:12px 14px; border:1px solid var(--portal-mblb-detail-border); border-radius:10px;">
                <div style="font-size:12px; color:var(--portal-mblb-detail-text-muted); text-transform:uppercase; letter-spacing:.04em;">Instansi</div>
                <div style="font-weight:600; color:var(--portal-mblb-detail-heading);">{{ $record->instansi_nama ?? '-' }}</div>
                <div style="font-size:13px; color:var(--portal-mblb-detail-text);">{{ $record->instansi_kategori?->getLabel() ?? 'Tidak dipilih' }}</div>
            </div>
        </div>
    </div>

    @if($record->notes)
        <div style="padding:12px 14px; border:1px solid var(--portal-mblb-detail-border); border-radius:10px; background:var(--portal-mblb-detail-bg-muted);">
            <div style="font-weight:700; color:var(--portal-mblb-detail-heading); margin-bottom:6px;">Keterangan Wajib Pajak</div>
            <div style="color:var(--portal-mblb-detail-text); white-space:pre-wrap;">{{ $record->notes }}</div>
        </div>
    @endif

    <div>
        <div style="font-weight:700; font-size:1rem; color:var(--portal-mblb-detail-heading); margin-bottom:8px;">Detail Mineral</div>
        <div style="border:1px solid var(--portal-mblb-detail-border); border-radius:10px; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <thead style="background:var(--portal-mblb-detail-bg-muted); color:var(--portal-mblb-detail-text); text-align:left;">
                    <tr>
                        <th style="padding:12px 14px;">Mineral</th>
                        <th style="padding:12px 14px;">Volume</th>
                        <th style="padding:12px 14px;">Harga Patokan</th>
                        <th style="padding:12px 14px;">Subtotal DPP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailItems as $detail)
                        <tr style="border-top:1px solid var(--portal-mblb-detail-border);">
                            <td style="padding:12px 14px; color:var(--portal-mblb-detail-heading);">{{ $detail['jenis_mblb'] ?? '-' }}</td>
                            <td style="padding:12px 14px; color:var(--portal-mblb-detail-text);">{{ number_format((float) ($detail['volume'] ?? 0), 2, ',', '.') }} m3</td>
                            <td style="padding:12px 14px; color:var(--portal-mblb-detail-text);">Rp {{ number_format((float) ($detail['harga_patokan'] ?? 0), 0, ',', '.') }}</td>
                            <td style="padding:12px 14px; color:var(--portal-mblb-detail-text);">Rp {{ number_format((float) ($detail['subtotal_dpp'] ?? 0), 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:8px;">
            <div style="font-weight:700; font-size:1rem; color:var(--portal-mblb-detail-heading);">Lampiran Pendukung</div>
            @if($attachmentUrl)
                <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener" style="font-size:13px; color:var(--portal-mblb-detail-link); font-weight:600;">Buka lampiran</a>
            @endif
        </div>
        @if($attachmentUrl)
            @if($isPdf)
                <iframe src="{{ $attachmentUrl }}" style="width:100%; height:480px; border:1px solid var(--portal-mblb-detail-border); border-radius:10px;"></iframe>
            @else
                <img src="{{ $attachmentUrl }}" alt="Lampiran pengajuan MBLB" style="width:100%; max-height:480px; object-fit:contain; border:1px solid var(--portal-mblb-detail-border); border-radius:10px; background:var(--portal-mblb-detail-bg-muted);">
            @endif
        @else
            <div style="padding:12px 14px; border:1px solid var(--portal-mblb-detail-border); border-radius:10px; color:var(--portal-mblb-detail-text-muted);">Lampiran tidak tersedia.</div>
        @endif
    </div>

    <div style="--verification-history-border: var(--portal-mblb-detail-border); --verification-history-bg: transparent; --verification-history-heading: var(--portal-mblb-detail-heading); --verification-history-text: var(--portal-mblb-detail-text); --verification-history-muted: var(--portal-mblb-detail-text-muted); --verification-history-accent: var(--portal-mblb-detail-link); --verification-history-line: var(--portal-mblb-detail-border);">
        <x-verification-status-timeline
            :histories="$record->verificationStatusHistories"
            heading="Riwayat Status Verifikasi"
            empty-message="Belum ada riwayat status untuk pengajuan ini."
        />
    </div>
</div>