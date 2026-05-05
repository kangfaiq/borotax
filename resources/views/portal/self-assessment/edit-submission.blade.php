@extends('layouts.portal-dashboard')

@section('title', 'Perbaiki Pengajuan MBLB - Borotax Portal')
@section('page-title', 'Perbaiki Pengajuan MBLB')

@section('styles')
    <style>
        .mblb-edit-wrap {
            max-width: 980px;
            margin: 0 auto;
        }

        .mblb-edit-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            color: var(--text-tertiary);
            font-size: 0.84rem;
            font-weight: 700;
        }

        .mblb-edit-alert,
        .mblb-edit-card {
            border-radius: var(--radius-lg);
            background: var(--bg-card);
            border: 1px solid var(--border);
        }

        .mblb-edit-alert {
            padding: 20px 22px;
            margin-bottom: 18px;
            border-color: #fecaca;
            background: #fef2f2;
        }

        .mblb-edit-alert h2 {
            margin-bottom: 8px;
            font-size: 1.05rem;
            font-weight: 800;
            color: #b91c1c;
        }

        .mblb-edit-alert p {
            margin: 0;
            color: #b91c1c;
            line-height: 1.7;
            font-size: 0.86rem;
        }

        .mblb-edit-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 18px;
        }

        .mblb-edit-card {
            padding: 22px;
            margin-bottom: 18px;
        }

        .mblb-edit-card h3 {
            margin-bottom: 14px;
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--text-primary);
        }

        .mblb-edit-stat {
            padding: 16px 18px;
            border-radius: var(--radius-md);
            background: var(--bg-surface);
            border: 1px solid var(--border);
        }

        .mblb-edit-stat .label {
            display: block;
            margin-bottom: 4px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-tertiary);
        }

        .mblb-edit-stat .value {
            display: block;
            font-size: 0.92rem;
            font-weight: 800;
            color: var(--text-primary);
        }

        .mblb-edit-form-group {
            margin-bottom: 18px;
        }

        .mblb-edit-form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .mblb-edit-form-group p {
            margin-top: 6px;
            font-size: 0.76rem;
            color: var(--text-tertiary);
        }

        .mblb-edit-control {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            background: var(--bg-surface-variant);
            color: var(--text-primary);
            font-size: 0.88rem;
            font-family: inherit;
            outline: none;
        }

        .mblb-edit-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.12);
            background: var(--bg-card);
        }

        .mblb-edit-error {
            margin-top: 6px;
            font-size: 0.78rem;
            color: var(--error);
        }

        .mblb-edit-mineral-list {
            display: grid;
            gap: 12px;
        }

        .mblb-edit-mineral-row {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(130px, 0.7fr) minmax(160px, 0.8fr);
            gap: 12px;
            align-items: center;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: var(--bg-card);
        }

        .mblb-edit-mineral-name {
            font-size: 0.88rem;
            font-weight: 800;
            color: var(--text-primary);
        }

        .mblb-edit-mineral-meta {
            margin-top: 4px;
            font-size: 0.76rem;
            color: var(--text-tertiary);
        }

        .mblb-edit-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .mblb-edit-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            border: none;
            text-decoration: none;
            font-size: 0.84rem;
            font-weight: 700;
            cursor: pointer;
        }

        .mblb-edit-btn.primary { background: var(--primary); color: white; }
        .mblb-edit-btn.secondary { background: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border); }

        .portal-combobox {
            position: relative;
        }

        .portal-combobox-native {
            display: none;
        }

        .portal-combobox-input {
            padding-right: 44px;
        }

        .portal-combobox-clear {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            width: 24px;
            height: 24px;
            border: none;
            border-radius: 999px;
            background: transparent;
            color: var(--text-tertiary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .portal-combobox-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            z-index: 20;
            display: none;
            max-height: 260px;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: var(--bg-card);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
        }

        .portal-combobox.is-open .portal-combobox-menu {
            display: block;
        }

        .portal-combobox-option,
        .portal-combobox-empty {
            width: 100%;
            padding: 10px 14px;
            text-align: left;
            border: none;
            background: transparent;
            color: var(--text-primary);
            font-size: 0.84rem;
        }

        .portal-combobox-option {
            display: block;
            cursor: pointer;
        }

        .portal-combobox-option:hover,
        .portal-combobox-option.is-active {
            background: var(--primary-50);
            color: var(--primary-dark);
        }

        .portal-combobox-empty {
            display: none;
            color: var(--text-secondary);
        }

        @media (max-width: 768px) {
            .mblb-edit-grid,
            .mblb-edit-mineral-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    <div class="mblb-edit-wrap">
        <a href="{{ route('portal.mblb-submissions.show', $submission->id) }}" class="mblb-edit-back">
            <i class="bi bi-arrow-left"></i> Kembali ke Detail Pengajuan
        </a>

        <div class="mblb-edit-alert">
            <h2>Perbaiki Pengajuan yang Ditolak</h2>
            <p>{{ $submission->rejection_reason ?: 'Perbaiki data sesuai catatan verifikator, lalu kirim ulang pengajuan ini.' }}</p>
        </div>

        <div class="mblb-edit-grid">
            <div class="mblb-edit-stat">
                <span class="label">Objek Pajak</span>
                <span class="value">{{ $taxObject->nama_objek_pajak }}</span>
            </div>
            <div class="mblb-edit-stat">
                <span class="label">Masa Pajak</span>
                <span class="value">{{ $submission->masa_pajak_label }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('portal.mblb-submissions.update', $submission->id) }}" enctype="multipart/form-data">
            @csrf

            <div class="mblb-edit-card">
                <h3>Detail Mineral</h3>
                <div class="mblb-edit-form-group">
                    <label for="inputMineralSearch">Cari Jenis Material</label>
                    <input type="text" class="mblb-edit-control" id="inputMineralSearch" placeholder="Cari jenis material MBLB..." autocomplete="off">
                    <p>Ketik nama mineral dan daftar material akan langsung terfilter otomatis.</p>
                </div>
                <div class="mblb-edit-mineral-list">
                    @foreach($mineralItems as $item)
                        <div class="mblb-edit-mineral-row" data-mineral-label="{{ strtolower($item->nama_mineral . ' ' . $item->satuan) }}">
                            <div>
                                <div class="mblb-edit-mineral-name">{{ $item->nama_mineral }}</div>
                                <div class="mblb-edit-mineral-meta">Harga patokan Rp {{ number_format((float) $item->harga_patokan, 0, ',', '.') }}/{{ strtoupper($item->satuan) }}</div>
                            </div>
                            <div>
                                <label style="display:block; margin-bottom:4px; font-size:0.78rem; font-weight:700; color:var(--text-primary);">Volume (m3)</label>
                                <input type="text" class="mblb-edit-control"
                                    name="volumes[{{ $item->id }}]"
                                    value="{{ old('volumes.' . $item->id, $submissionVolumeMap[$item->id] ?? null) }}"
                                    inputmode="decimal"
                                    placeholder="0.00"
                                    autocomplete="off">
                            </div>
                            <div>
                                <div class="mblb-edit-mineral-meta">Satuan</div>
                                <div class="mblb-edit-mineral-name">{{ strtoupper($item->satuan) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('volumes')
                    <div class="mblb-edit-error">{{ $message }}</div>
                @enderror
            </div>

            @if(($taxObject->subJenisPajak?->kode ?? null) === 'MBLB_WAPU')
                <div class="mblb-edit-card">
                    <h3>Instansi / Lembaga</h3>
                    <div class="mblb-edit-form-group">
                        <label>Instansi Terkait</label>
                        <select class="portal-combobox-native" name="instansi_id" id="inputInstansiId">
                            <option value="">-- Opsional, pilih instansi --</option>
                            @foreach($instansiOptions as $instansi)
                                <option value="{{ $instansi->id }}" @selected(old('instansi_id', $submission->instansi_id) == $instansi->id)>
                                    {{ $instansi->nama }} ({{ $instansi->kategori?->getLabel() ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="portal-combobox" id="instansiCombobox">
                            <input type="text" class="mblb-edit-control portal-combobox-input" id="inputInstansiCombobox"
                                data-combobox-input placeholder="Cari instansi / lembaga..." autocomplete="off">
                            <button type="button" class="portal-combobox-clear" data-combobox-clear hidden aria-label="Hapus instansi terpilih">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <div class="portal-combobox-menu" data-combobox-menu>
                                <div data-combobox-options></div>
                                <div class="portal-combobox-empty" data-combobox-empty>
                                    Tidak ada instansi atau lembaga yang cocok dengan pencarian.
                                </div>
                            </div>
                        </div>
                        @error('instansi_id')
                            <div class="mblb-edit-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endif

            <div class="mblb-edit-card">
                <h3>Catatan & Lampiran</h3>
                <div class="mblb-edit-form-group">
                    <label for="inputKeterangan">Keterangan</label>
                    <textarea class="mblb-edit-control" name="keterangan" id="inputKeterangan" rows="4">{{ old('keterangan', $submission->notes) }}</textarea>
                    <p>Keterangan ini akan ikut dikirim kembali ke verifikator.</p>
                    @error('keterangan')
                        <div class="mblb-edit-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mblb-edit-form-group">
                    <label for="inputAttachment">Lampiran Pendukung</label>
                    <input type="file" class="mblb-edit-control" name="attachment" id="inputAttachment" accept=".jpg,.jpeg,.png,.pdf">
                    <p>
                        Upload hanya jika ingin mengganti lampiran sebelumnya.
                        @if($submission->attachment_url)
                            Lampiran saat ini bisa dibuka melalui
                            <a href="{{ $submission->attachment_url }}" target="_blank">tautan ini</a>.
                        @endif
                    </p>
                    @error('attachment')
                        <div class="mblb-edit-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mblb-edit-actions">
                <a href="{{ route('portal.mblb-submissions.show', $submission->id) }}" class="mblb-edit-btn secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
                <button type="submit" class="mblb-edit-btn primary">
                    <i class="bi bi-send"></i> Kirim Ulang Pengajuan
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inputMineralSearch = document.getElementById('inputMineralSearch');
            const mineralRows = Array.from(document.querySelectorAll('.mblb-edit-mineral-row'));
            const inputInstansiId = document.getElementById('inputInstansiId');

            function normalizeSearchKeyword(value) {
                return String(value ?? '').trim().toLowerCase();
            }

            function filterMineralRows(query) {
                const keyword = normalizeSearchKeyword(query);

                mineralRows.forEach(function (row) {
                    const label = row.dataset.mineralLabel || '';
                    row.style.display = keyword === '' || label.includes(keyword) ? '' : 'none';
                });
            }

            function buildComboboxOption(label, isActive) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'portal-combobox-option' + (isActive ? ' is-active' : '');
                button.textContent = label;

                return button;
            }

            function setupPortalCombobox() {
                const root = document.getElementById('instansiCombobox');

                if (!root || !inputInstansiId) {
                    return;
                }

                const input = root.querySelector('[data-combobox-input]');
                const clearButton = root.querySelector('[data-combobox-clear]');
                const optionsContainer = root.querySelector('[data-combobox-options]');
                const emptyState = root.querySelector('[data-combobox-empty]');
                const options = Array.from(inputInstansiId.options)
                    .filter(function (option) {
                        return option.value !== '';
                    })
                    .map(function (option) {
                        return {
                            value: String(option.value),
                            label: option.textContent.trim(),
                            searchLabel: normalizeSearchKeyword(option.textContent),
                        };
                    });
                let selectedValue = String(inputInstansiId.value || '');

                function syncInputFromSelection() {
                    const selectedOption = options.find(function (option) {
                        return option.value === selectedValue;
                    });

                    input.value = selectedOption ? selectedOption.label : '';
                }

                function updateClearButton() {
                    clearButton.hidden = normalizeSearchKeyword(input.value) === '' && selectedValue === '';
                }

                function renderOptions() {
                    const keyword = normalizeSearchKeyword(input.value);
                    const filteredOptions = options
                        .filter(function (option) {
                            return keyword === '' || option.searchLabel.includes(keyword);
                        })
                        .slice(0, 50);

                    optionsContainer.innerHTML = '';

                    const resetButton = buildComboboxOption('-- Opsional, pilih instansi --', selectedValue === '' && keyword === '');
                    resetButton.addEventListener('mousedown', function (event) {
                        event.preventDefault();
                        input.value = '';
                        inputInstansiId.value = '';
                        selectedValue = '';
                        renderOptions();
                        updateClearButton();
                        root.classList.remove('is-open');
                    });
                    optionsContainer.appendChild(resetButton);

                    filteredOptions.forEach(function (option) {
                        const optionButton = buildComboboxOption(option.label, option.value === selectedValue);
                        optionButton.addEventListener('mousedown', function (event) {
                            event.preventDefault();
                            input.value = option.label;
                            inputInstansiId.value = option.value;
                            selectedValue = option.value;
                            renderOptions();
                            updateClearButton();
                            root.classList.remove('is-open');
                        });
                        optionsContainer.appendChild(optionButton);
                    });

                    emptyState.style.display = filteredOptions.length === 0 ? 'block' : 'none';
                }

                input.addEventListener('focus', function () {
                    root.classList.add('is-open');
                    renderOptions();
                });

                input.addEventListener('input', function () {
                    selectedValue = '';
                    inputInstansiId.value = '';
                    root.classList.add('is-open');
                    renderOptions();
                    updateClearButton();
                });

                clearButton.addEventListener('click', function () {
                    input.value = '';
                    inputInstansiId.value = '';
                    selectedValue = '';
                    renderOptions();
                    updateClearButton();
                    input.focus();
                });

                document.addEventListener('click', function (event) {
                    if (root.contains(event.target)) {
                        return;
                    }

                    root.classList.remove('is-open');
                    syncInputFromSelection();
                    updateClearButton();
                });

                syncInputFromSelection();
                renderOptions();
                updateClearButton();
            }

            if (inputMineralSearch) {
                inputMineralSearch.addEventListener('input', function () {
                    filterMineralRows(this.value);
                });
            }

            filterMineralRows('');
            setupPortalCombobox();
        });
    </script>
@endsection