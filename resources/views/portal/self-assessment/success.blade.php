@extends('layouts.portal-dashboard')

@section('title', 'Billing Berhasil - Borotax Portal')
@section('page-title', 'Billing Berhasil')

@section('styles')
    <style>
        .success-page {
            max-width: 620px;
        }

        .success-card {
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .success-header {
            background: linear-gradient(135deg, var(--success), #16A34A);
            padding: 28px;
            text-align: center;
            color: white;
        }

        .success-header .check-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 12px;
        }

        .success-header h2 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .success-header p {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .success-body {
            padding: 28px;
        }

        /* Billing code box */
        .billing-code-box {
            background: var(--primary-50);
            border: 1.5px solid var(--primary);
            border-radius: var(--radius-md);
            padding: 16px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .billing-code-box .bc-label {
            font-size: 0.78rem;
            color: var(--text-tertiary);
            margin-bottom: 4px;
        }

        .billing-code-box .bc-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--primary-dark);
            letter-spacing: 1px;
            font-family: monospace;
        }

        .billing-code-box .bc-copy {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            padding: 8px 14px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            font-family: inherit;
            transition: all var(--transition);
        }

        .billing-code-box .bc-copy:hover {
            background: var(--primary-dark);
        }

        /* Info rows */
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-light);
            font-size: 0.85rem;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row .label {
            color: var(--text-secondary);
        }

        .info-row .value {
            font-weight: 600;
            color: var(--text-primary);
        }

        /* Total */
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 16px 0;
            border-top: 1.5px solid var(--border);
            margin-top: 8px;
        }

        .total-row .label {
            font-weight: 700;
            color: var(--text-primary);
        }

        .total-row .value {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--primary-dark);
        }

        /* Payment channels */
        .payment-box {
            background: var(--info-light);
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: var(--radius-md);
            padding: 18px;
            margin-top: 20px;
        }

        .payment-box h4 {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--info);
            margin-bottom: 12px;
        }

        .payment-channel {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 0;
            font-size: 0.85rem;
            color: var(--text-primary);
        }

        .payment-channel i {
            color: var(--info);
            width: 18px;
            text-align: center;
        }

        /* Actions */
        .success-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-outline {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            background: var(--bg-card);
            color: var(--text-secondary);
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition);
            text-decoration: none;
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary-dark);
        }

        .btn-primary-full {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition);
            text-decoration: none;
        }

        .btn-primary-full:hover {
            background: var(--primary-dark);
            color: white;
        }
    </style>
@endsection

@section('content')
    <div class="success-page">
        <div class="success-card">
            <div class="success-header">
                <div class="check-icon"><i class="bi bi-check-lg"></i></div>
                <h2>Billing Berhasil Dibuat!</h2>
                <p>Simpan kode billing untuk pembayaran</p>
            </div>

            <div class="success-body">
                {{-- Billing Code --}}
                <div class="billing-code-box">
                    <div>
                        <div class="bc-label">Kode Billing</div>
                        <div class="bc-value" id="billingCode">{{ $tax->billing_code }}</div>
                    </div>
                    <button type="button" class="bc-copy" onclick="copyBilling()">
                        <i class="bi bi-clipboard"></i> Salin
                    </button>
                </div>

                {{-- Detail --}}
                @if($taxObject)
                    <div class="info-row">
                        <span class="label">Objek Pajak</span>
                        <span class="value">{{ $taxObject->nama_objek_pajak }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">NPWPD</span>
                        <span class="value" style="font-family:monospace;">{{ $taxObject->npwpd }}</span>
                    </div>
                @endif
                <div class="info-row">
                    <span class="label">Jenis Pajak</span>
                    <span class="value">{{ $tax->jenisPajak->nama ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Masa Pajak</span>
                    <span
                        class="value">{{ $tax->masa_pajak_bulan && $tax->masa_pajak_tahun ? \Carbon\Carbon::create($tax->masa_pajak_tahun, $tax->masa_pajak_bulan, 1)->translatedFormat('F Y') : ($tax->masa_pajak_tahun ? 'Tahun ' . $tax->masa_pajak_tahun : $tax->created_at->translatedFormat('F Y')) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Berlaku Hingga</span>
                    <span
                        class="value">{{ $tax->payment_expired_at ? $tax->payment_expired_at->format('d/m/Y H:i') : '7 hari' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Dokumen Lampiran</span>
                    <span class="value" style="color: var(--success);"><i class="bi bi-check-circle-fill"></i>
                        Terlampir</span>
                </div>

                <div class="total-row">
                    <span class="label">Total Pajak</span>
                    <span class="value">Rp {{ number_format((float) $tax->amount, 0, ',', '.') }}</span>
                </div>

                {{-- Payment Channels --}}
                <div class="payment-box">
                    <h4><i class="bi bi-info-circle"></i> Tempat Pembayaran</h4>
                    <div class="payment-channel"><i class="bi bi-bank"></i> Bank Jatim (Teller / mBanking)</div>
                    <div class="payment-channel"><i class="bi bi-shop"></i> Indomaret</div>
                    <div class="payment-channel"><i class="bi bi-shop-window"></i> Alfamart</div>
                    <div class="payment-channel"><i class="bi bi-bag-check"></i> Tokopedia</div>
                    <div class="payment-channel"><i class="bi bi-qr-code"></i> QRIS (via E-Payment)</div>
                </div>

                {{-- Print / Download --}}
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <a href="{{ route('portal.billing.document.show', $tax->id) }}" target="_blank"
                        title="{{ $tax->getBillingDocumentActionTitle() }}"
                        style="flex:1; display:inline-flex; align-items:center; justify-content:center; gap:6px; padding:12px; background:#3b82f6; color:#fff; border-radius:var(--radius-md); font-size:0.85rem; font-weight:600; text-decoration:none; transition:all var(--transition);">
                        <i class="bi bi-printer"></i> {{ $tax->getBillingDocumentActionLabel() }}
                    </a>
                    <a href="{{ route('portal.billing.document.download', $tax->id) }}"
                        title="{{ $tax->getBillingDownloadActionTitle() }}"
                        style="flex:1; display:inline-flex; align-items:center; justify-content:center; gap:6px; padding:12px; background:#10b981; color:#fff; border-radius:var(--radius-md); font-size:0.85rem; font-weight:600; text-decoration:none; transition:all var(--transition);">
                        <i class="bi bi-download"></i> {{ $tax->getBillingDownloadActionLabel() }}
                    </a>
                </div>

                {{-- Actions --}}
                <div class="success-actions">
                    <a href="{{ route('portal.history') }}" class="btn-outline">
                        <i class="bi bi-clock-history"></i> Riwayat
                    </a>
                    <a href="{{ route('portal.pembetulan.create', $tax->id) }}" class="btn-outline"
                        style="border-color: #f59e0b; color: #92400e;">
                        <i class="bi bi-pencil-square"></i> Ajukan Pembetulan
                    </a>
                    <a href="{{ route('portal.self-assessment.index') }}" class="btn-primary-full">
                        <i class="bi bi-plus-lg"></i> Buat Billing Lagi
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function copyBilling() {
            const code = document.getElementById('billingCode').textContent.trim();

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(code).then(onCopySuccess).catch(function () {
                    fallbackCopy(code);
                });
            } else {
                fallbackCopy(code);
            }
        }

        function fallbackCopy(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-9999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
                onCopySuccess();
            } catch (err) {
                console.error('Unable to copy', err);
                alert('Gagal menyalin. Silakan salin manual: ' + text);
            }

            document.body.removeChild(textArea);
        }

        function onCopySuccess() {
            const btn = document.querySelector('.bc-copy');
            const originalHtml = btn.innerHTML;

            // Prevent double click/animation issues
            if (btn.innerHTML.includes('Tersalin')) return;

            btn.innerHTML = '<i class="bi bi-check-lg"></i> Tersalin!';

            setTimeout(function () {
                btn.innerHTML = '<i class="bi bi-clipboard"></i> Salin';
            }, 2000);
        }
    </script>
@endsection