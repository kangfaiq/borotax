<div class="histori-box">

                <form wire:submit.prevent="cari" novalidate>
                    <input id="hp-turnstile-token" type="hidden" wire:model.defer="turnstileToken">

                    <div class="form-grid">
                        <div>
                            <label class="form-label" for="hp-npwpd">NPWPD (P1/P2 + 11 digit)</label>
                            <input id="hp-npwpd" type="text" class="form-input"
                                   maxlength="13" wire:model.defer="npwpd" placeholder="Contoh: P100000000001" style="text-transform:uppercase;">
                            @error('npwpd') <div class="alert-error">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label" for="hp-tahun">Tahun Pajak</label>
                            <select id="hp-tahun" class="form-select" wire:model.defer="tahun">
                                @foreach($daftarTahun as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                            @error('tahun') <div class="alert-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    @if(config('services.turnstile.key'))
                        <div style="margin-top:16px;" wire:ignore>
                            <div class="cf-turnstile"
                                 data-sitekey="{{ config('services.turnstile.key') }}"
                                 data-callback="onTurnstileSuccess"
                                 data-theme="auto"></div>
                        </div>
                    @endif

                    <div class="submit-row">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="cari">
                            <span wire:loading.remove wire:target="cari"><i class="bi bi-search"></i> Cari Histori</span>
                            <span wire:loading wire:target="cari">Memproses...</span>
                        </button>
                        @if($sudahCari)
                            <span style="color: var(--text-secondary); font-size:0.85rem;">
                                NPWPD <strong>{{ $npwpd }}</strong> &middot; Tahun <strong>{{ $tahun }}</strong>
                            </span>
                        @endif
                    </div>
                </form>

                @if($errorMessage)
                    <div class="alert-error" style="margin-top:18px;">{{ $errorMessage }}</div>
                @endif

                @if($sudahCari)
                    <div class="summary-cards">
                        <div class="summary-card">
                            <div class="label">Total Dokumen</div>
                            <div class="value">{{ number_format($ringkasan['total_dokumen']) }}</div>
                        </div>
                        <div class="summary-card tagihan">
                            <div class="label">Total Tagihan</div>
                            <div class="value">Rp {{ number_format($ringkasan['total_tagihan'], 0, ',', '.') }}</div>
                        </div>
                        <div class="summary-card terbayar">
                            <div class="label">Total Terbayar</div>
                            <div class="value">Rp {{ number_format($ringkasan['total_terbayar'], 0, ',', '.') }}</div>
                        </div>
                        <div class="summary-card tunggakan">
                            <div class="label">Total Tunggakan</div>
                            <div class="value">Rp {{ number_format($ringkasan['total_tunggakan'], 0, ',', '.') }}</div>
                        </div>
                    </div>

                    @if(count($rows) > 0)
                        <div class="actions-bar">
                            <button type="button" class="btn-action" id="copy-histori-pajak-button">
                                <i class="bi bi-clipboard"></i> Salin untuk Excel
                            </button>
                            <a class="btn-action" href="{{ route('histori-pajak.pdf', ['npwpd' => $npwpd, 'tahun' => $tahun]) }}" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-printer"></i> Cetak PDF (F4 Landscape)
                            </a>
                        </div>

                        <div id="histori-pajak-copy-feedback" class="alert-success" style="display:none;"></div>

                        <div class="table-wrapper">
                            <table class="histori-table" id="histori-pajak-table">
                                <thead>
                                    <tr>
                                        <th>Jenis Dokumen</th>
                                        <th>Jenis Pajak</th>
                                        <th>NOPD</th>
                                        <th>Objek Pajak</th>
                                        <th>Nomor</th>
                                        <th>Masa</th>
                                        <th class="col-tanggal-terbit">Terbit</th>
                                        <th class="col-jatuh-tempo">Jatuh Tempo</th>
                                        <th class="col-tanggal-bayar">Tanggal Bayar</th>
                                        <th class="text-right">Tagihan</th>
                                        <th class="text-right col-terbayar">Terbayar</th>
                                        <th class="text-right">Sisa</th>
                                        <th class="col-status">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $row)
                                        <tr @class(['row-overdue' => ($row['status'] ?? '') === 'lewat_jatuh_tempo'])>
                                            <td><span class="badge badge-{{ $row['jenis_dokumen_color'] }}">{{ $row['jenis_dokumen_label'] }}</span></td>
                                            <td>{{ $row['jenis_pajak'] }}</td>
                                            <td>{{ $row['nopd'] ?? '-' }}</td>
                                            <td>{{ $row['nama_objek_pajak'] ?? '-' }}</td>
                                            <td><code>{{ $row['nomor'] }}</code></td>
                                            <td>{{ $row['masa'] }}</td>
                                            <td class="col-tanggal-terbit">{{ $row['tanggal_terbit'] ?? '-' }}</td>
                                            <td class="col-jatuh-tempo">{{ $row['jatuh_tempo'] ?? '-' }}</td>
                                            <td class="col-tanggal-bayar">{{ $row['tanggal_bayar'] ?? '-' }}</td>
                                            <td class="text-right" data-copy-value="{{ $row['jumlah_tagihan'] }}">Rp {{ number_format($row['jumlah_tagihan'], 0, ',', '.') }}</td>
                                            <td class="text-right col-terbayar" data-copy-value="{{ $row['jumlah_terbayar'] }}">Rp {{ number_format($row['jumlah_terbayar'], 0, ',', '.') }}</td>
                                            <td class="text-right" data-copy-value="{{ $row['jumlah_sisa'] }}">Rp {{ number_format($row['jumlah_sisa'], 0, ',', '.') }}</td>
                                            <td class="col-status">
                                                @if(($row['status'] ?? '') === 'lewat_jatuh_tempo')
                                                    <span class="badge-overdue">{{ $row['status_label'] }}</span>
                                                @else
                                                    {{ $row['status_label'] }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="bi bi-inbox" style="font-size:2rem;"></i>
                            <p style="margin-top:10px;">Tidak ada dokumen pajak ditemukan untuk NPWPD dan tahun tersebut.</p>
                        </div>
                    @endif
                @endif
            </div>
