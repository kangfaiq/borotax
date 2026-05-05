@php
    $detailItems = collect($record->detail_items ?? []);
    $attachmentUrl = $record->attachment_path
        ? route('portal.mblb-submissions.attachment', $record)
        : null;
    $isPdf = str_ends_with(strtolower($record->attachment_path ?? ''), '.pdf');
@endphp

<div style="display:grid; gap:16px; max-width:860px;">
    <div>
        <div style="font-weight:700; font-size:1rem; color:#0f172a; margin-bottom:8px;">Ringkasan Pengajuan</div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
            <div style="padding:12px 14px; border:1px solid #e2e8f0; border-radius:10px;">
                <div style="font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:.04em;">Wajib Pajak</div>
                <div style="font-weight:600; color:#0f172a;">{{ $record->user?->nama_lengkap ?? '-' }}</div>
                <div style="font-size:13px; color:#475569;">{{ $record->user?->nik ?? '-' }}</div>
            </div>
            <div style="padding:12px 14px; border:1px solid #e2e8f0; border-radius:10px;">
                <div style="font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:.04em;">Objek Pajak</div>
                <div style="font-weight:600; color:#0f172a;">{{ $record->taxObject?->nama_objek_pajak ?? '-' }}</div>
                <div style="font-size:13px; color:#475569;">NPWPD {{ $record->taxObject?->npwpd ?? '-' }}</div>
            </div>
            <div style="padding:12px 14px; border:1px solid #e2e8f0; border-radius:10px;">
                <div style="font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:.04em;">Masa Pajak</div>
                <div style="font-weight:600; color:#0f172a;">{{ $record->masa_pajak_label }}</div>
                <div style="font-size:13px; color:#475569;">Tarif {{ number_format((float) $record->tarif_persen, 0) }}% + Opsen {{ number_format((float) $record->opsen_persen, 0) }}%</div>
            </div>
            <div style="padding:12px 14px; border:1px solid #e2e8f0; border-radius:10px;">
                <div style="font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:.04em;">Estimasi Tagihan</div>
                <div style="font-weight:700; color:#0f172a;">Rp {{ number_format($record->total_tagihan, 0, ',', '.') }}</div>
                <div style="font-size:13px; color:#475569;">DPP Rp {{ number_format((float) $record->total_dpp, 0, ',', '.') }}</div>
            </div>
            <div style="padding:12px 14px; border:1px solid #e2e8f0; border-radius:10px;">
                <div style="font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:.04em;">Instansi</div>
                <div style="font-weight:600; color:#0f172a;">{{ $record->instansi_nama ?? '-' }}</div>
                <div style="font-size:13px; color:#475569;">{{ $record->instansi_kategori?->getLabel() ?? 'Tidak dipilih' }}</div>
            </div>
        </div>
    </div>

    @if($record->notes)
        <div style="padding:12px 14px; border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc;">
            <div style="font-weight:700; color:#0f172a; margin-bottom:6px;">Keterangan Wajib Pajak</div>
            <div style="color:#334155; white-space:pre-wrap;">{{ $record->notes }}</div>
        </div>
    @endif

    <div>
        <div style="font-weight:700; font-size:1rem; color:#0f172a; margin-bottom:8px;">Detail Mineral</div>
        <div style="border:1px solid #e2e8f0; border-radius:10px; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <thead style="background:#f8fafc; color:#475569; text-align:left;">
                    <tr>
                        <th style="padding:12px 14px;">Mineral</th>
                        <th style="padding:12px 14px;">Volume</th>
                        <th style="padding:12px 14px;">Harga Patokan</th>
                        <th style="padding:12px 14px;">Subtotal DPP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailItems as $detail)
                        <tr style="border-top:1px solid #e2e8f0;">
                            <td style="padding:12px 14px; color:#0f172a;">{{ $detail['jenis_mblb'] ?? '-' }}</td>
                            <td style="padding:12px 14px; color:#334155;">{{ number_format((float) ($detail['volume'] ?? 0), 2, ',', '.') }} m3</td>
                            <td style="padding:12px 14px; color:#334155;">Rp {{ number_format((float) ($detail['harga_patokan'] ?? 0), 0, ',', '.') }}</td>
                            <td style="padding:12px 14px; color:#334155;">Rp {{ number_format((float) ($detail['subtotal_dpp'] ?? 0), 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:8px;">
            <div style="font-weight:700; font-size:1rem; color:#0f172a;">Lampiran Pendukung</div>
            @if($attachmentUrl)
                <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener" style="font-size:13px; color:#2563eb; font-weight:600;">Buka lampiran</a>
            @endif
        </div>
        @if($attachmentUrl)
            @if($isPdf)
                <iframe src="{{ $attachmentUrl }}" style="width:100%; height:480px; border:1px solid #e2e8f0; border-radius:10px;"></iframe>
            @else
                <img src="{{ $attachmentUrl }}" alt="Lampiran pengajuan MBLB" style="width:100%; max-height:480px; object-fit:contain; border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc;">
            @endif
        @else
            <div style="padding:12px 14px; border:1px solid #e2e8f0; border-radius:10px; color:#64748b;">Lampiran tidak tersedia.</div>
        @endif
    </div>
</div>