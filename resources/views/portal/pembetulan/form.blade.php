@extends('layouts.portal-dashboard')

@section('title', 'Ajukan Pembetulan - Borotax Portal')
@section('page-title', 'Ajukan Pembetulan')

@section('styles')
<style>
    .pemb-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        color: var(--text-tertiary);
        margin-bottom: 20px;
        transition: color var(--transition);
    }
    .pemb-back:hover { color: var(--primary-dark); }

    .pemb-card {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        padding: 28px;
        margin-bottom: 20px;
    }

    .pemb-card-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pemb-card-title i { color: var(--primary); }

    .billing-summary {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .billing-summary .item {
        padding: 12px 16px;
        background: var(--bg-surface);
        border-radius: var(--radius-md);
    }

    .billing-summary .item .label {
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .billing-summary .item .value {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .billing-summary .item.full { grid-column: 1 / -1; }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 10px;
        border-radius: var(--radius-full);
        font-size: 0.72rem;
        font-weight: 700;
    }
    .status-badge.pending { background: #fef3c7; color: #92400e; }
    .status-badge.paid { background: #d1fae5; color: #065f46; }
    .status-badge.verified { background: #dbeafe; color: #1e40af; }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 6px;
    }

    .form-group label .req { color: var(--error); }
    .form-group label .opt { color: var(--text-tertiary); font-weight: 400; }

    .form-control {
        width: 100%;
        padding: 11px 14px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-md);
        font-size: 0.88rem;
        font-family: inherit;
        background: var(--bg-surface-variant);
        transition: all var(--transition);
        outline: none;
        color: var(--text-primary);
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.12);
        background: var(--bg-card);
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .form-hint {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        margin-top: 4px;
    }

    .form-error {
        color: var(--error);
        font-size: 0.75rem;
        margin-top: 4px;
    }

    .input-prefix {
        display: flex;
        align-items: center;
    }

    .input-prefix .prefix {
        padding: 11px 14px;
        background: var(--bg-surface);
        border: 1.5px solid var(--border);
        border-right: none;
        border-radius: var(--radius-md) 0 0 var(--radius-md);
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .input-prefix .form-control {
        border-radius: 0 var(--radius-md) var(--radius-md) 0;
    }

    .btn-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 14px 28px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-family: inherit;
        font-size: 0.92rem;
        font-weight: 700;
        cursor: pointer;
        transition: all var(--transition);
    }

    .btn-submit:hover {
        background: var(--primary-dark);
        box-shadow: 0 6px 20px rgba(var(--primary-rgb), 0.35);
    }

    .alert-warning {
        background: #fffbeb;
        color: #92400e;
        border: 1px solid #fbbf24;
        border-radius: var(--radius-md);
        padding: 14px 18px;
        margin-bottom: 20px;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .alert-error {
        background: var(--error-light);
        color: #C62828;
        border: 1px solid rgba(239,68,68,0.2);
        border-radius: var(--radius-md);
        padding: 14px 18px;
        margin-bottom: 20px;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    @media (max-width: 768px) {
        .billing-summary { grid-template-columns: 1fr; }
        .pemb-card { padding: 20px; }
    }
</style>
@endsection

@section('content')
    <a href="{{ route('portal.history') }}" class="pemb-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Riwayat
    </a>

    @if($errors->any())
        <div class="alert-error">
            <i class="bi bi-exclamation-circle"></i>
            {{ $errors->first() }}
        </div>
    @endif

    @if($existingRequest)
        <div class="alert-warning">
            <i class="bi bi-clock-history"></i>
            Anda sudah mengajukan permohonan pembetulan untuk billing ini pada
            {{ $existingRequest->created_at->translatedFormat('d F Y H:i') }}.
            Status: <strong>{{ ucfirst($existingRequest->status) }}</strong>
        </div>
    @endif

    {{-- Info Billing yang akan dikoreksi --}}
    <div class="pemb-card">
        <div class="pemb-card-title">
            <i class="bi bi-receipt"></i> Data Billing yang Akan Dikoreksi
        </div>
        @php
            $statusClass = match($tax->status) {
                App\Enums\TaxStatus::Paid => 'paid',
                App\Enums\TaxStatus::Verified => 'verified',
                default => 'pending',
            };
            $statusLabel = match($tax->status) {
                App\Enums\TaxStatus::Paid => 'Sudah Dibayar',
                App\Enums\TaxStatus::Verified => 'Terverifikasi',
                default => 'Belum Dibayar',
            };
        @endphp
        <div class="billing-summary">
            <div class="item">
                <div class="label">Kode Billing</div>
                <div class="value" style="font-family: monospace;">{{ $tax->billing_code }}</div>
            </div>
            <div class="item">
                <div class="label">Status</div>
                <div class="value"><span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span></div>
            </div>
            <div class="item">
                <div class="label">Jenis Pajak</div>
                <div class="value">{{ $tax->jenisPajak->nama ?? '-' }}</div>
            </div>
            <div class="item">
                <div class="label">Objek Pajak</div>
                <div class="value">{{ $tax->taxObject->nama_objek_pajak ?? '-' }}</div>
            </div>
            <div class="item">
                <div class="label">Masa Pajak</div>
                <div class="value">{{ $tax->masa_pajak_bulan ? \Carbon\Carbon::create($tax->masa_pajak_tahun, $tax->masa_pajak_bulan, 1)->translatedFormat('F Y') : 'Tahun ' . $tax->masa_pajak_tahun }}</div>
            </div>
            <div class="item">
                <div class="label">Omzet</div>
                <div class="value">Rp {{ number_format($tax->omzet, 0, ',', '.') }}</div>
            </div>
            <div class="item full">
                <div class="label">Total Pajak</div>
                <div class="value" style="color: var(--primary-dark); font-size: 1.1rem;">Rp {{ number_format($tax->amount, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    {{-- Form Pengajuan Pembetulan --}}
    @unless($existingRequest)
    <form method="POST" action="{{ route('portal.pembetulan.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="tax_id" value="{{ $tax->id }}">

        <div class="pemb-card">
            <div class="pemb-card-title">
                <i class="bi bi-pencil-square"></i> Form Pengajuan Pembetulan
            </div>

            <div class="form-group">
                <label>Alasan Pembetulan <span class="req">*</span></label>
                <textarea class="form-control" name="alasan" placeholder="Jelaskan alasan mengapa Anda ingin melakukan pembetulan billing ini..." required>{{ old('alasan') }}</textarea>
                <div class="form-hint">Minimal 10 karakter. Contoh: "Omzet yang dilaporkan keliru, seharusnya lebih besar/kecil."</div>
                @error('alasan')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Omzet Koreksi <span class="opt">(opsional)</span></label>
                <div class="input-prefix">
                    <span class="prefix">Rp</span>
                    <input type="text" class="form-control" name="omzet_baru_display" id="inputOmzetBaru"
                           placeholder="0" inputmode="numeric" value="{{ old('omzet_baru_display') }}" autocomplete="off">
                </div>
                <input type="hidden" name="omzet_baru" id="omzetBaruReal" value="{{ old('omzet_baru') }}">
                <div class="form-hint">Isi jika Anda ingin menyarankan omzet yang benar. Petugas akan meninjau.</div>
                @error('omzet_baru')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Lampiran Pendukung <span class="opt">(opsional)</span></label>
                <input type="file" class="form-control" name="lampiran" accept=".jpg,.jpeg,.png,.pdf">
                <div class="form-hint">Maksimal 1MB. Format: JPG, PNG, atau PDF. Dokumen pendukung alasan pembetulan.</div>
                @error('lampiran')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn-submit">
            <i class="bi bi-send"></i> Ajukan Permohonan Pembetulan
        </button>
    </form>
    @endunless
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var omzetInput = document.getElementById('inputOmzetBaru');
        var omzetReal = document.getElementById('omzetBaruReal');

        if (omzetInput) {
            omzetInput.addEventListener('input', function() {
                var raw = this.value.replace(/\D/g, '');
                this.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                omzetReal.value = raw ? parseInt(raw) : '';
            });
        }
    });
</script>
@endsection
