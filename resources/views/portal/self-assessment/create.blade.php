@extends('layouts.portal-dashboard')

@section('title', 'Self Assessment - {{ $jenisPajak->nama }} - Borotax Portal')
@section('page-title', 'Self Assessment')

@section('styles')
    <style>
        .sa-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: var(--text-tertiary);
            margin-bottom: 20px;
            transition: color var(--transition);
        }

        .sa-back:hover {
            color: var(--primary-dark);
        }

        .sa-form-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 28px;
        }

        .sa-form-header .fh-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-md);
            background: var(--primary-50);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .sa-form-header h2 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 2px;
        }

        .sa-form-header p {
            font-size: 0.82rem;
            color: var(--text-tertiary);
        }

        /* Form card */
        .form-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            padding: 28px;
            margin-bottom: 20px;
        }

        .form-card-title {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-card-title i {
            color: var(--primary);
        }

        /* Tax object selector */
        .tax-obj-list {
            display: grid;
            gap: 10px;
        }

        .tax-obj-radio {
            display: none;
        }

        .tax-obj-label {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            border-radius: var(--radius-md);
            border: 1.5px solid var(--border);
            cursor: pointer;
            transition: all var(--transition);
            background: var(--bg-card);
        }

        .tax-obj-label:hover {
            border-color: var(--primary-light);
            background: var(--primary-50);
        }

        .tax-obj-radio:checked+.tax-obj-label {
            border-color: var(--primary);
            background: var(--primary-50);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        .tax-obj-label .to-radio-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid var(--border);
            flex-shrink: 0;
            position: relative;
            transition: all var(--transition);
        }

        .tax-obj-radio:checked+.tax-obj-label .to-radio-dot {
            border-color: var(--primary);
        }

        .tax-obj-radio:checked+.tax-obj-label .to-radio-dot::after {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--primary);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .tax-obj-label .to-info {
            flex: 1;
            min-width: 0;
        }

        .tax-obj-label .to-name {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .tax-obj-label .to-meta {
            font-size: 0.75rem;
            color: var(--text-tertiary);
            margin-top: 2px;
        }

        .tax-obj-label .to-tarif {
            font-size: 0.72rem;
            font-weight: 700;
            background: var(--primary-50);
            color: var(--primary-dark);
            padding: 2px 10px;
            border-radius: var(--radius-full);
        }

        .no-objects {
            text-align: center;
            padding: 32px;
            color: var(--text-tertiary);
        }

        .no-objects i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
            color: var(--warning);
        }

        /* Form fields */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

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

        .form-group label .req {
            color: var(--error);
        }

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

        .form-control::placeholder {
            color: var(--text-tertiary);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%2394A3B8' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
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

        /* File upload */
        .file-upload-area {
            border: 2px dashed var(--border);
            border-radius: var(--radius-md);
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all var(--transition);
            position: relative;
        }

        .file-upload-area:hover {
            border-color: var(--primary);
            background: var(--primary-50);
        }

        .file-upload-area i {
            font-size: 1.8rem;
            color: var(--text-tertiary);
            margin-bottom: 8px;
            display: block;
        }

        .file-upload-area p {
            font-size: 0.82rem;
            color: var(--text-tertiary);
        }

        .file-upload-area p strong {
            color: var(--primary-dark);
        }

        .file-upload-area input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-selected {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: var(--success-light);
            border-radius: var(--radius-md);
            border: 1px solid rgba(34, 197, 94, 0.2);
            margin-top: 10px;
        }

        .file-selected i {
            color: var(--success);
        }

        .file-selected span {
            flex: 1;
            font-size: 0.82rem;
            color: var(--text-primary);
            font-weight: 500;
        }

        /* Calculation box */
        .calc-box {
            background: var(--bg-surface);
            border-radius: var(--radius-md);
            padding: 18px;
            margin-top: 6px;
        }

        .calc-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 0.85rem;
        }

        .calc-row .label {
            color: var(--text-secondary);
        }

        .calc-row .value {
            font-weight: 600;
            color: var(--text-primary);
        }

        .calc-row.total {
            border-top: 1.5px solid var(--border);
            padding-top: 12px;
            margin-top: 6px;
        }

        .calc-row.total .value {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--primary-dark);
        }

        .mineral-list {
            display: grid;
            gap: 12px;
        }

        .mineral-row {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(140px, 0.8fr) minmax(160px, 0.8fr);
            gap: 12px;
            align-items: center;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: var(--bg-card);
        }

        .mineral-name {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .mineral-meta {
            margin-top: 3px;
            font-size: 0.75rem;
            color: var(--text-tertiary);
        }

        .mineral-price {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--primary-dark);
            text-align: right;
        }

        .mblb-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 14px 16px;
            border-radius: var(--radius-md);
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            font-size: 0.82rem;
            margin-bottom: 16px;
        }

        .mblb-note i {
            font-size: 1rem;
            margin-top: 2px;
        }

        /* Masa pajak auto */
        .masa-pajak-auto {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            background: var(--primary-50);
            border: 1.5px solid var(--primary);
            border-radius: var(--radius-md);
        }

        .masa-pajak-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-md);
            background: rgba(var(--primary-rgb), 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: var(--primary-dark);
            flex-shrink: 0;
        }

        .masa-pajak-label {
            font-size: 0.75rem;
            color: var(--text-tertiary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .masa-pajak-value {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--primary-dark);
        }

        .masa-pajak-value .text-muted {
            color: var(--text-tertiary);
            font-weight: 500;
            font-size: 0.88rem;
        }

        .masa-pajak-note {
            margin-top: 10px;
            font-size: 0.78rem;
            color: var(--text-tertiary);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .masa-pajak-note i {
            color: var(--info);
            font-size: 0.85rem;
        }

        .masa-pajak-manual-note {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.82rem;
            color: #1e40af;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }

        .masa-pajak-manual-note i {
            font-size: 1rem;
        }

        .form-row {
            display: flex;
            gap: 16px;
        }

        .form-row .form-group {
            flex: 1;
        }

        /* Submit button */
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

        .btn-submit:disabled {
            background: var(--border);
            color: var(--text-tertiary);
            cursor: not-allowed;
            box-shadow: none;
        }

        /* Error */
        .form-error {
            color: var(--error);
            font-size: 0.75rem;
            margin-top: 4px;
        }

        .alert-error {
            background: var(--error-light);
            color: #C62828;
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: var(--radius-md);
            padding: 14px 18px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-card {
                padding: 20px;
            }

            .mineral-row {
                grid-template-columns: 1fr;
            }

            .mineral-price {
                text-align: left;
            }
        }
    </style>
@endsection

@section('content')
    <a href="{{ route('portal.self-assessment.index') }}" class="sa-back">
        <i class="bi bi-arrow-left"></i> Kembali ke pilihan jenis pajak
    </a>

    <div class="sa-form-header">
        <div class="fh-icon">{{ $jenisPajak->icon }}</div>
        <div>
            <h2>{{ $jenisPajak->nama }}</h2>
            <p>{{ $jenisPajak->deskripsi }}</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert-error">
            <i class="bi bi-exclamation-circle"></i>
            {{ $errors->first() }}
        </div>
    @endif

    @if($taxObjects->isEmpty())
        <div class="form-card">
            <div class="no-objects">
                <i class="bi bi-building-exclamation"></i>
                <p><strong>Tidak Ada Objek Pajak</strong></p>
                <p>Anda belum memiliki objek pajak {{ strtolower($jenisPajak->nama) }} yang terdaftar.</p>
                <p style="margin-top:12px; font-size:0.8rem;">Silakan hubungi kantor Bapenda untuk mendaftarkan objek pajak
                    Anda.</p>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('portal.self-assessment.store') }}" enctype="multipart/form-data" id="saForm">
            @csrf

            {{-- 1. Pilih Objek Pajak --}}
            <div class="form-card">
                <div class="form-card-title">
                    <i class="bi bi-building"></i> Pilih Objek Pajak
                </div>
                <div class="tax-obj-list">
                    @foreach($taxObjects as $obj)
                        <div>
                            <input type="radio" name="tax_object_id" value="{{ $obj->id }}" class="tax-obj-radio"
                                id="obj_{{ $obj->id }}" data-tarif="{{ $obj->tarif_persen }}"
                                data-sub-jenis-kode="{{ $obj->subJenisPajak->kode ?? '' }}"
                                data-next-bulan="{{ $nextPeriods[$obj->id]['bulan'] }}"
                                data-next-tahun="{{ $nextPeriods[$obj->id]['tahun'] }}"
                                data-next-label="{{ $nextPeriods[$obj->id]['label'] }}"
                                data-is-new="{{ $nextPeriods[$obj->id]['isNew'] ? '1' : '0' }}"
                                data-is-opd="{{ $obj->is_opd ? '1' : '0' }}"
                                data-is-insidentil="{{ $obj->is_insidentil ? '1' : '0' }}"
                                data-is-yearly="{{ ($nextPeriods[$obj->id]['isYearly'] ?? false) ? '1' : '0' }}" @checked(old('tax_object_id') == $obj->id || $taxObjects->count() == 1)>
                            <label for="obj_{{ $obj->id }}" class="tax-obj-label">
                                <span class="to-radio-dot"></span>
                                <div class="to-info">
                                    <div class="to-name">{{ $obj->nama_objek_pajak }}</div>
                                    <div class="to-meta">
                                        NPWPD: {{ $obj->npwpd }} &bull;
                                        {{ $obj->subJenisPajak->nama ?? '' }} &bull;
                                        {{ $obj->kecamatan }}
                                    </div>
                                </div>
                                <span class="to-tarif">{{ number_format($obj->tarif_persen, 0) }}%</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('tax_object_id')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- 2. Masa Pajak --}}
            <div class="form-card">
                <div class="form-card-title">
                    <i class="bi bi-calendar3"></i> Masa Pajak
                </div>

                {{-- Mode Otomatis (objek lama) --}}
                <div id="masaPajakAutoMode" style="display:none;">
                    <div class="masa-pajak-auto">
                        <div class="masa-pajak-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="masa-pajak-info">
                            <div class="masa-pajak-label">Masa Pajak Berikutnya</div>
                            <div class="masa-pajak-value" id="masaPajakDisplay">-</div>
                        </div>
                    </div>
                    <div class="masa-pajak-note">
                        <i class="bi bi-info-circle"></i>
                        Masa pajak ditentukan otomatis berdasarkan periode terakhir yang telah dilaporkan.
                    </div>
                </div>

                {{-- Mode Manual (objek baru) --}}
                <div id="masaPajakManualMode" style="display:none;">
                    <div class="masa-pajak-manual-note">
                        <i class="bi bi-pencil-square"></i>
                        Objek pajak baru &mdash; pilih masa pajak awal secara manual.
                    </div>
                    <div class="form-row">
                        <div class="form-group" id="manualBulanGroup">
                            <label>Bulan <span class="req">*</span></label>
                            <select class="form-control" id="inputBulanManual">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" @selected(old('bulan', now()->month) == $m)>
                                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tahun <span class="req">*</span></label>
                            <select class="form-control" id="inputTahunManual">
                                @foreach(range(date('Y'), 2020) as $y)
                                    <option value="{{ $y }}" @selected(old('tahun', date('Y')) == $y)>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Placeholder sebelum objek dipilih --}}
                <div id="masaPajakPlaceholder">
                    <div class="masa-pajak-auto" style="border-color: var(--border); background: var(--bg-surface);">
                        <div class="masa-pajak-icon" style="background: var(--bg-surface-variant);">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <div class="masa-pajak-info">
                            <div class="masa-pajak-label">Masa Pajak</div>
                            <div class="masa-pajak-value"><span class="text-muted">Pilih objek pajak terlebih dahulu</span>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="bulan" id="inputBulan" value="">
                <input type="hidden" name="tahun" id="inputTahun" value="">
            </div>

            {{-- 3. Data Perhitungan --}}
            @if($isSarangWalet)
                {{-- Sarang Walet: Jenis Sarang + Volume --}}
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="bi bi-feather"></i> Data Sarang & Perhitungan Pajak
                    </div>
                    <div class="form-group">
                        <label>Jenis Sarang <span class="req">*</span></label>
                        <select class="form-control" name="jenis_sarang_id" id="inputJenisSarang">
                            <option value="">-- Pilih Jenis Sarang --</option>
                            @foreach($jenisSarangWalet as $js)
                                <option value="{{ $js->id }}" data-hpu="{{ $js->harga_patokan }}" @selected(old('jenis_sarang_id') == $js->id)>
                                    {{ $js->nama_jenis }} — Rp {{ number_format((float)$js->harga_patokan, 0, ',', '.') }}/{{ $js->satuan }}
                                </option>
                            @endforeach
                        </select>
                        @error('jenis_sarang_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Volume <span class="req">*</span></label>
                        <div class="input-prefix">
                            <input type="number" class="form-control" id="inputVolumeKg" name="volume_kg" placeholder="0.00"
                                step="0.01" min="0.01" max="999999.99" value="{{ old('volume_kg') }}" autocomplete="off">
                            <span class="prefix" style="border-left:none; border-right:1.5px solid var(--border); border-radius:0 var(--radius-md) var(--radius-md) 0;">kg</span>
                        </div>
                        @error('volume_kg')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="calc-box" id="calcBoxSw" style="display:none;">
                        <div class="calc-row">
                            <span class="label">HPU (Harga Patokan Umum)</span>
                            <span class="value" id="calcHpu">Rp 0</span>
                        </div>
                        <div class="calc-row">
                            <span class="label">Volume</span>
                            <span class="value" id="calcVolume">0 kg</span>
                        </div>
                        <div class="calc-row">
                            <span class="label">DPP (HPU × Volume)</span>
                            <span class="value" id="calcDpp">Rp 0</span>
                        </div>
                        <div class="calc-row">
                            <span class="label">Tarif Pajak</span>
                            <span class="value" id="calcTarifSw">10%</span>
                        </div>
                        <div class="calc-row total">
                            <span class="label">Estimasi Pajak Terutang</span>
                            <span class="value" id="calcTotalSw">Rp 0</span>
                        </div>
                    </div>
                </div>
            @elseif($isPpj)
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="bi bi-lightning-charge"></i> Data PPJ & Perhitungan Pajak
                    </div>
                    <div class="mblb-note">
                        <i class="bi bi-info-circle"></i>
                        <div>
                            Form PPJ akan menyesuaikan objek yang dipilih: PLN memakai input pokok pajak langsung,
                            sedangkan non-PLN memakai komponen kapasitas, tingkat penggunaan, jangka waktu, dan harga satuan listrik.
                        </div>
                    </div>

                    @if($isPpjPln)
                        <div id="ppjPlnSection" style="display:none;">
                            <div class="form-group">
                                <label>Pokok Pajak Terutang <span class="req">*</span></label>
                                <div class="input-prefix">
                                    <span class="prefix">Rp</span>
                                    <input type="text" class="form-control" id="inputPpjPokokPajakDisplay"
                                        name="pokok_pajak_display" placeholder="0" inputmode="numeric"
                                        value="{{ old('pokok_pajak') ? number_format((float) old('pokok_pajak'), 0, ',', '.') : '' }}"
                                        autocomplete="off">
                                </div>
                                <input type="hidden" name="pokok_pajak" id="inputPpjPokokPajak"
                                    value="{{ old('pokok_pajak') }}">
                                <p style="font-size:0.75rem; color: var(--text-tertiary); margin-top: 4px;">
                                    Masukkan pokok pajak sesuai tagihan listrik dari sumber lain.
                                </p>
                                @error('pokok_pajak')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="calc-box" id="calcBoxPpjPln" style="display:none;">
                                <div class="calc-row">
                                    <span class="label">Pokok Pajak</span>
                                    <span class="value" id="calcPpjPlnPokok">Rp 0</span>
                                </div>
                                <div class="calc-row">
                                    <span class="label">DPP (back-calculate)</span>
                                    <span class="value" id="calcPpjPlnDpp">Rp 0</span>
                                </div>
                                <div class="calc-row">
                                    <span class="label">Tarif Pajak</span>
                                    <span class="value" id="calcPpjPlnTarif">0%</span>
                                </div>
                                <div class="calc-row total">
                                    <span class="label">Estimasi Billing</span>
                                    <span class="value" id="calcPpjPlnTotal">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($isPpjNonPln)
                        <div id="ppjNonPlnSection" style="display:none;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Kapasitas Tersedia (kVA) <span class="req">*</span></label>
                                    <input type="number" class="form-control" id="inputKapasitasKva"
                                        name="kapasitas_kva" min="0.01" step="0.01"
                                        value="{{ old('kapasitas_kva') }}" placeholder="0.00" autocomplete="off">
                                    @error('kapasitas_kva')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Tingkat Penggunaan (%) <span class="req">*</span></label>
                                    <input type="number" class="form-control" id="inputTingkatPenggunaan"
                                        name="tingkat_penggunaan_persen" min="0.01" max="100" step="0.01"
                                        value="{{ old('tingkat_penggunaan_persen') }}" placeholder="0.00" autocomplete="off">
                                    @error('tingkat_penggunaan_persen')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Jangka Waktu Pemakaian (jam) <span class="req">*</span></label>
                                    <input type="number" class="form-control" id="inputJangkaWaktuJam"
                                        name="jangka_waktu_jam" min="0.01" step="0.01"
                                        value="{{ old('jangka_waktu_jam') }}" placeholder="0.00" autocomplete="off">
                                    @error('jangka_waktu_jam')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Harga Satuan Listrik <span class="req">*</span></label>
                                    <select class="form-control" name="harga_satuan_listrik_id" id="inputHargaSatuanListrik">
                                        <option value="">-- Pilih Harga Satuan Listrik --</option>
                                        @foreach($hargaSatuanListrikItems as $item)
                                            <option value="{{ $item->id }}"
                                                data-harga-satuan="{{ (float) $item->harga_per_kwh }}"
                                                @selected(old('harga_satuan_listrik_id') == $item->id)>
                                                {{ $item->nama_wilayah }} — Rp {{ number_format((float) $item->harga_per_kwh, 0, ',', '.') }}/kWh
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('harga_satuan_listrik_id')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="calc-box" id="calcBoxPpjNonPln" style="display:none;">
                                <div class="calc-row">
                                    <span class="label">Harga Satuan Listrik</span>
                                    <span class="value" id="calcPpjHargaSatuan">Rp 0</span>
                                </div>
                                <div class="calc-row">
                                    <span class="label">NJTL</span>
                                    <span class="value" id="calcPpjNjtl">Rp 0</span>
                                </div>
                                <div class="calc-row">
                                    <span class="label">DPP</span>
                                    <span class="value" id="calcPpjDpp">Rp 0</span>
                                </div>
                                <div class="calc-row">
                                    <span class="label">Tarif Pajak</span>
                                    <span class="value" id="calcPpjNonPlnTarif">0%</span>
                                </div>
                                <div class="calc-row total">
                                    <span class="label">Estimasi Pokok Pajak</span>
                                    <span class="value" id="calcPpjNonPlnTotal">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @elseif($isMblb)
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="bi bi-box-seam"></i> Data Mineral & Estimasi Pajak
                    </div>
                    <div class="mblb-note">
                        <i class="bi bi-shield-check"></i>
                        <div>
                            Isi volume per mineral yang diproduksi pada masa pajak ini. Pengajuan akan diverifikasi lebih dulu
                            sebelum kode billing diterbitkan.
                        </div>
                    </div>
                    <div class="mineral-list">
                        @foreach($mineralItems as $item)
                            <div class="mineral-row">
                                <div>
                                    <div class="mineral-name">{{ $item->nama_mineral }}</div>
                                    <div class="mineral-meta">Harga patokan Rp {{ number_format((float) $item->harga_patokan, 0, ',', '.') }}/{{ $item->satuan }}</div>
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label style="margin-bottom:4px;">Volume (m3)</label>
                                    <input type="number" class="form-control mineral-volume-input"
                                        name="volumes[{{ $item->id }}]"
                                        value="{{ old('volumes.' . $item->id) }}"
                                        min="0"
                                        step="0.01"
                                        max="999999.99"
                                        data-harga-patokan="{{ (float) $item->harga_patokan }}"
                                        placeholder="0.00"
                                        autocomplete="off">
                                </div>
                                <div class="mineral-price">
                                    {{ strtoupper($item->satuan) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('volumes')
                        <div class="form-error">{{ $message }}</div>
                    @enderror

                    <div class="calc-box" id="calcBoxMblb" style="display:none; margin-top:16px;">
                        <div class="calc-row">
                            <span class="label">Mineral Terisi</span>
                            <span class="value" id="calcMineralCount">0 item</span>
                        </div>
                        <div class="calc-row">
                            <span class="label">Total Volume</span>
                            <span class="value" id="calcMblbVolume">0 m3</span>
                        </div>
                        <div class="calc-row">
                            <span class="label">DPP</span>
                            <span class="value" id="calcMblbDpp">Rp 0</span>
                        </div>
                        <div class="calc-row">
                            <span class="label">Pokok Pajak</span>
                            <span class="value" id="calcMblbPokok">Rp 0</span>
                        </div>
                        <div class="calc-row">
                            <span class="label">Opsen</span>
                            <span class="value" id="calcMblbOpsen">Rp 0</span>
                        </div>
                        <div class="calc-row total">
                            <span class="label">Estimasi Tagihan</span>
                            <span class="value" id="calcMblbTotal">Rp 0</span>
                        </div>
                    </div>
                </div>
            @else
                {{-- Standard: Omzet --}}
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="bi bi-cash-stack"></i> Omzet & Perhitungan Pajak
                    </div>
                    <div class="form-group">
                        <label>Omzet Bulan Ini <span class="req">*</span></label>
                        <div class="input-prefix">
                            <span class="prefix">Rp</span>
                            <input type="text" class="form-control" id="inputOmzet" name="omzet_display" placeholder="0"
                                inputmode="numeric" value="{{ old('omzet_display') }}" autocomplete="off">
                        </div>
                        <input type="hidden" name="omzet" id="omzetReal" value="{{ old('omzet', 0) }}">
                        @error('omzet')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="calc-box" id="calcBox" style="display:none;">
                        <div class="calc-row">
                            <span class="label">Omzet</span>
                            <span class="value" id="calcOmzet">Rp 0</span>
                        </div>
                        <div class="calc-row">
                            <span class="label">Tarif Pajak</span>
                            <span class="value" id="calcTarif">10%</span>
                        </div>
                        <div class="calc-row total">
                            <span class="label">Estimasi Pajak Terutang</span>
                            <span class="value" id="calcTotal">Rp 0</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 4. Dokumen Lampiran --}}
            <div class="form-card">
                <div class="form-card-title">
                    <i class="bi bi-paperclip"></i> Dokumen Lampiran
                </div>
                <div class="form-group">
                    <label>Upload Bukti (foto/scan) <span class="req">*</span></label>
                    <div class="file-upload-area" id="fileUploadArea">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <p><strong>Klik untuk upload</strong> atau drag & drop</p>
                        <p style="font-size:0.72rem; margin-top:4px;">
                            @if($isMblb)
                                JPG, PNG, PDF &bull; PDF maks 1MB, gambar otomatis dikompres
                            @else
                                JPG, PNG, PDF &bull; Maks 1MB
                            @endif
                        </p>
                        <input type="file" name="attachment" id="inputAttachment" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                    <div class="file-selected" id="fileSelected" style="display:none;">
                        <i class="bi bi-check-circle-fill"></i>
                        <span id="fileName">-</span>
                    </div>
                    @error('attachment')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- 5. Keterangan (hanya untuk OPD) --}}
            <div class="form-card" id="keteranganCard" style="display:none;">
                <div class="form-card-title">
                    <i class="bi bi-journal-text"></i> Keterangan
                </div>
                <div class="form-group">
                    <label>Keterangan Billing <span class="req">*</span></label>
                    <textarea class="form-control" name="keterangan" id="inputKeterangan" rows="3"
                        placeholder="Contoh: Katering Rapat Dinas DPRD, Pertunjukan HUT Kab. Bojonegoro, dll."
                        style="resize: vertical;">{{ old('keterangan') }}</textarea>
                    <p style="font-size: 0.75rem; color: var(--text-tertiary); margin-top: 4px;">
                        <i class="bi bi-info-circle"></i> Wajib diisi karena dalam satu masa pajak dapat memiliki
                        lebih dari satu billing.
                    </p>
                    @error('keterangan')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>


            {{-- Submit --}}
            <button type="submit" class="btn-submit" id="btnSubmit">
                <i class="bi bi-receipt"></i> {{ $isMblb ? 'Ajukan Verifikasi Billing' : 'Generate Billing' }}
            </button>
        </form>
    @endif
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isSarangWalet = {{ $isSarangWalet ? 'true' : 'false' }};
            const isPpj = {{ $isPpj ? 'true' : 'false' }};
            const isMblb = {{ $isMblb ? 'true' : 'false' }};
            const opsenPersen = {{ (float) ($opsenPersen ?? 25) }};
            const omzetInput = document.getElementById('inputOmzet');
            const omzetReal = document.getElementById('omzetReal');
            const calcBox = document.getElementById('calcBox');
            const calcOmzet = document.getElementById('calcOmzet');
            const calcTarif = document.getElementById('calcTarif');
            const calcTotal = document.getElementById('calcTotal');
            const fileInput = document.getElementById('inputAttachment');
            const fileSelected = document.getElementById('fileSelected');
            const fileName = document.getElementById('fileName');

            // Sarang Walet elements
            const jenisSarangSelect = document.getElementById('inputJenisSarang');
            const volumeInput = document.getElementById('inputVolumeKg');
            const calcBoxSw = document.getElementById('calcBoxSw');

            // PPJ elements
            const ppjPlnSection = document.getElementById('ppjPlnSection');
            const ppjNonPlnSection = document.getElementById('ppjNonPlnSection');
            const inputPpjPokokPajakDisplay = document.getElementById('inputPpjPokokPajakDisplay');
            const inputPpjPokokPajak = document.getElementById('inputPpjPokokPajak');
            const inputKapasitasKva = document.getElementById('inputKapasitasKva');
            const inputTingkatPenggunaan = document.getElementById('inputTingkatPenggunaan');
            const inputJangkaWaktuJam = document.getElementById('inputJangkaWaktuJam');
            const inputHargaSatuanListrik = document.getElementById('inputHargaSatuanListrik');
            const calcBoxPpjPln = document.getElementById('calcBoxPpjPln');
            const calcBoxPpjNonPln = document.getElementById('calcBoxPpjNonPln');

            const mineralInputs = Array.from(document.querySelectorAll('.mineral-volume-input'));
            const calcBoxMblb = document.getElementById('calcBoxMblb');

            function getSelectedTarif() {
                const checked = document.querySelector('input[name="tax_object_id"]:checked');
                return checked ? parseFloat(checked.dataset.tarif) : 10;
            }

            function getSelectedSubJenisKode() {
                const checked = document.querySelector('input[name="tax_object_id"]:checked');
                return checked ? checked.dataset.subJenisKode : '';
            }

            function isPpjPlnSelected() {
                return getSelectedSubJenisKode() === 'PPJ_SUMBER_LAIN';
            }

            function isPpjNonPlnSelected() {
                return getSelectedSubJenisKode() === 'PPJ_DIHASILKAN_SENDIRI';
            }

            function formatRp(num) {
                return 'Rp ' + num.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function formatThousands(val) {
                val = val.replace(/\D/g, '');
                return val.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function formatVolume(num) {
                return Number(num).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2,
                });
            }

            function updatePpjSectionVisibility() {
                if (!isPpj) return;

                if (ppjPlnSection) {
                    ppjPlnSection.style.display = isPpjPlnSelected() ? 'block' : 'none';
                }

                if (ppjNonPlnSection) {
                    ppjNonPlnSection.style.display = isPpjNonPlnSelected() ? 'block' : 'none';
                }
            }

            function recalcPpj() {
                if (!isPpj) return;

                updatePpjSectionVisibility();

                if (calcBoxPpjPln) {
                    calcBoxPpjPln.style.display = 'none';
                }

                if (calcBoxPpjNonPln) {
                    calcBoxPpjNonPln.style.display = 'none';
                }

                const tarif = getSelectedTarif();

                if (isPpjPlnSelected()) {
                    const pokokPajak = parseFloat(inputPpjPokokPajak?.value) || 0;
                    const dpp = tarif > 0 ? Math.round(pokokPajak / (tarif / 100)) : 0;

                    if (pokokPajak > 0 && calcBoxPpjPln) {
                        calcBoxPpjPln.style.display = 'block';
                        document.getElementById('calcPpjPlnPokok').textContent = formatRp(pokokPajak);
                        document.getElementById('calcPpjPlnDpp').textContent = formatRp(dpp);
                        document.getElementById('calcPpjPlnTarif').textContent = tarif + '%';
                        document.getElementById('calcPpjPlnTotal').textContent = formatRp(pokokPajak);
                    }

                    return;
                }

                if (isPpjNonPlnSelected()) {
                    const kapasitasKva = parseFloat(inputKapasitasKva?.value) || 0;
                    const tingkatPenggunaan = parseFloat(inputTingkatPenggunaan?.value) || 0;
                    const jangkaWaktuJam = parseFloat(inputJangkaWaktuJam?.value) || 0;
                    const hargaSatuanOption = inputHargaSatuanListrik
                        ? inputHargaSatuanListrik.options[inputHargaSatuanListrik.selectedIndex]
                        : null;
                    const hargaSatuan = hargaSatuanOption && hargaSatuanOption.dataset.hargaSatuan
                        ? parseFloat(hargaSatuanOption.dataset.hargaSatuan)
                        : 0;
                    const njtl = Math.round(kapasitasKva * (tingkatPenggunaan / 100) * jangkaWaktuJam * hargaSatuan);
                    const pokokPajak = Math.round(njtl * (tarif / 100));

                    if (kapasitasKva > 0 && tingkatPenggunaan > 0 && jangkaWaktuJam > 0 && hargaSatuan > 0 && calcBoxPpjNonPln) {
                        calcBoxPpjNonPln.style.display = 'block';
                        document.getElementById('calcPpjHargaSatuan').textContent = formatRp(hargaSatuan);
                        document.getElementById('calcPpjNjtl').textContent = formatRp(njtl);
                        document.getElementById('calcPpjDpp').textContent = formatRp(njtl);
                        document.getElementById('calcPpjNonPlnTarif').textContent = tarif + '%';
                        document.getElementById('calcPpjNonPlnTotal').textContent = formatRp(pokokPajak);
                    }
                }
            }

            function recalcMblb() {
                if (!isMblb || !calcBoxMblb) return;

                const tarif = getSelectedTarif();
                let mineralCount = 0;
                let totalVolume = 0;
                let totalDpp = 0;

                mineralInputs.forEach(function(input) {
                    const volume = parseFloat(input.value) || 0;
                    const hargaPatokan = parseFloat(input.dataset.hargaPatokan) || 0;

                    if (volume <= 0) {
                        return;
                    }

                    mineralCount++;
                    totalVolume += volume;
                    totalDpp += Math.round(volume * hargaPatokan);
                });

                const pokok = Math.round(totalDpp * (tarif / 100));
                const opsen = Math.round(pokok * (opsenPersen / 100));
                const total = pokok + opsen;

                if (mineralCount > 0) {
                    calcBoxMblb.style.display = 'block';
                    document.getElementById('calcMineralCount').textContent = mineralCount + ' item';
                    document.getElementById('calcMblbVolume').textContent = formatVolume(totalVolume) + ' m3';
                    document.getElementById('calcMblbDpp').textContent = formatRp(totalDpp);
                    document.getElementById('calcMblbPokok').textContent = formatRp(pokok);
                    document.getElementById('calcMblbOpsen').textContent = formatRp(opsen);
                    document.getElementById('calcMblbTotal').textContent = formatRp(total);
                } else {
                    calcBoxMblb.style.display = 'none';
                }
            }

            // --- Sarang Walet calculation ---
            function recalcSarangWalet() {
                if (!isSarangWalet || !jenisSarangSelect || !volumeInput) return;
                var opt = jenisSarangSelect.options[jenisSarangSelect.selectedIndex];
                var hpu = opt && opt.dataset.hpu ? parseFloat(opt.dataset.hpu) : 0;
                var vol = parseFloat(volumeInput.value) || 0;
                var tarif = getSelectedTarif();
                var dpp = hpu * vol;
                var pajak = dpp * (tarif / 100);

                if (hpu > 0 && vol > 0) {
                    calcBoxSw.style.display = 'block';
                    document.getElementById('calcHpu').textContent = formatRp(hpu);
                    document.getElementById('calcVolume').textContent = vol + ' kg';
                    document.getElementById('calcDpp').textContent = formatRp(dpp);
                    document.getElementById('calcTarifSw').textContent = tarif + '%';
                    document.getElementById('calcTotalSw').textContent = formatRp(pajak);
                } else {
                    calcBoxSw.style.display = 'none';
                }
            }

            if (jenisSarangSelect) jenisSarangSelect.addEventListener('change', recalcSarangWalet);
            if (volumeInput) volumeInput.addEventListener('input', recalcSarangWalet);
            if (inputPpjPokokPajakDisplay) {
                inputPpjPokokPajakDisplay.addEventListener('input', function () {
                    const raw = this.value.replace(/\D/g, '');
                    this.value = formatThousands(raw);
                    inputPpjPokokPajak.value = raw ? parseInt(raw, 10) : '';
                    recalcPpj();
                });
            }
            if (inputKapasitasKva) inputKapasitasKva.addEventListener('input', recalcPpj);
            if (inputTingkatPenggunaan) inputTingkatPenggunaan.addEventListener('input', recalcPpj);
            if (inputJangkaWaktuJam) inputJangkaWaktuJam.addEventListener('input', recalcPpj);
            if (inputHargaSatuanListrik) inputHargaSatuanListrik.addEventListener('change', recalcPpj);
            mineralInputs.forEach(function(input) {
                input.addEventListener('input', recalcMblb);
            });

            // --- Standard omzet calculation ---
            if (omzetInput) {
                omzetInput.addEventListener('input', function () {
                    const raw = this.value.replace(/\D/g, '');
                    this.value = formatThousands(raw);
                    const omzet = parseInt(raw) || 0;
                    omzetReal.value = omzet;

                    const tarif = getSelectedTarif();
                    const tax = omzet * (tarif / 100);

                    if (omzet > 0) {
                        calcBox.style.display = 'block';
                        calcOmzet.textContent = formatRp(omzet);
                        calcTarif.textContent = tarif + '%';
                        calcTotal.textContent = formatRp(tax);
                    } else {
                        calcBox.style.display = 'none';
                    }
                });
            }

            // Re-calc and update masa pajak when tax object changes
            var masaPajakAutoEl = document.getElementById('masaPajakAutoMode');
            var masaPajakManualEl = document.getElementById('masaPajakManualMode');
            var masaPajakPlaceholder = document.getElementById('masaPajakPlaceholder');
            var masaPajakDisplay = document.getElementById('masaPajakDisplay');
            var inputBulan = document.getElementById('inputBulan');
            var inputTahun = document.getElementById('inputTahun');
            var inputBulanManual = document.getElementById('inputBulanManual');
            var inputTahunManual = document.getElementById('inputTahunManual');
            var manualBulanGroup = document.getElementById('manualBulanGroup');

            function showMasaPajakMode(mode, radio) {
                masaPajakAutoEl.style.display = (mode === 'auto') ? 'block' : 'none';
                masaPajakManualEl.style.display = (mode === 'manual') ? 'block' : 'none';
                masaPajakPlaceholder.style.display = (mode === 'placeholder') ? 'block' : 'none';

                // For yearly masa pajak (sarang walet), hide bulan selector
                var isYearly = radio && radio.dataset.isYearly === '1';
                if (manualBulanGroup) {
                    manualBulanGroup.style.display = isYearly ? 'none' : '';
                }

                if (mode === 'auto' && radio) {
                    masaPajakDisplay.innerHTML = radio.dataset.nextLabel;
                    inputBulan.value = radio.dataset.nextBulan;
                    inputTahun.value = radio.dataset.nextTahun;
                } else if (mode === 'manual') {
                    inputBulan.value = isYearly ? '' : (inputBulanManual ? inputBulanManual.value : '');
                    inputTahun.value = inputTahunManual ? inputTahunManual.value : '';
                } else {
                    inputBulan.value = '';
                    inputTahun.value = '';
                }
            }

            // Sync manual selects → hidden inputs on change
            if (inputBulanManual) {
                inputBulanManual.addEventListener('change', function () {
                    inputBulan.value = this.value;
                });
            }
            if (inputTahunManual) {
                inputTahunManual.addEventListener('change', function () {
                    inputTahun.value = this.value;
                });
            }

            document.querySelectorAll('input[name="tax_object_id"]').forEach(function(r) {
            r.addEventListener('change', function() {
                if (omzetInput) omzetInput.dispatchEvent(new Event('input'));
                if (isSarangWalet) recalcSarangWalet();
                if (isPpj) recalcPpj();
                if (isMblb) recalcMblb();

                var isNew = this.dataset.isNew === '1';
                if (isNew) {
                    showMasaPajakMode('manual', this);
                } else {
                    showMasaPajakMode('auto', this);
                }

                // Show/hide keterangan card for OPD or insidentil
                var keteranganCard = document.getElementById('keteranganCard');
                if (keteranganCard) {
                    keteranganCard.style.display = (this.dataset.isOpd === '1' || this.dataset.isInsidentil === '1') ? 'block' : 'none';
                }
            });
        });

            // Trigger masa pajak display on page load if one object is pre-selected
            var preSelected = document.querySelector('input[name="tax_object_id"]:checked');
            if (preSelected) {
                preSelected.dispatchEvent(new Event('change'));
                var keteranganCard = document.getElementById('keteranganCard');
                if (keteranganCard) {
                    keteranganCard.style.display = (preSelected.dataset.isOpd === '1' || preSelected.dataset.isInsidentil === '1') ? 'block' : 'none';
                }
            }

            // File input
            if (fileInput) {
                fileInput.addEventListener('change', function () {
                    if (this.files.length > 0) {
                        var file = this.files[0];
                        var maxSize = 1 * 1024 * 1024; // 1MB
                        var maxImageUploadSize = 8 * 1024 * 1024;
                        var isImage = file.type.startsWith('image/');

                        if ((isMblb && isImage && file.size > maxImageUploadSize) || (!isMblb || !isImage) && file.size > maxSize) {
                            alert(isMblb && isImage
                                ? 'Ukuran gambar maksimal 8MB sebelum kompresi. File yang dipilih: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB'
                                : 'Ukuran file maksimal 1MB. File yang dipilih: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB');
                            this.value = '';
                            fileSelected.style.display = 'none';
                            return;
                        }
                        fileSelected.style.display = 'flex';
                        fileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB'
                            + (isMblb && isImage ? ', akan dikompres otomatis' : '') + ')';
                    } else {
                        fileSelected.style.display = 'none';
                    }
                });
            }

            // Trigger initial calc if old value exists
            if (omzetInput && omzetInput.value) {
                omzetInput.dispatchEvent(new Event('input'));
            }
            // Trigger initial calc for sarang walet if old values exist
            if (isSarangWalet) recalcSarangWalet();
            if (isPpj) recalcPpj();
            if (isMblb) recalcMblb();
        });
    </script>
@endsection