@extends('layouts.portal-guest')

@section('title', 'Revisi Permohonan Sewa - Borotax Portal')

@section('navbar-class', 'navbar-light')

@section('nav-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/sewa-reklame') }}" style="color: var(--primary-dark); font-weight: 600;">Sewa Reklame</a>
    <a href="{{ url('/login') }}" class="btn btn-primary btn-sm navbar-cta">Login</a>
@endsection

@section('nav-mobile-links')
    <a href="{{ url('/') }}">Beranda</a>
    <a href="{{ url('/cek-billing') }}">Cek Billing</a>
    <a href="{{ url('/sewa-reklame') }}">Sewa Reklame</a>
    <a href="{{ url('/login') }}">Login Wajib Pajak</a>
@endsection

@section('styles')
<style>
    .obj-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        color: var(--text-tertiary);
        margin-bottom: 20px;
        transition: color var(--transition);
    }

    .obj-back:hover { color: var(--primary-dark); }

    /* Revision notice */
    .revision-notice {
        background: #E3F2FD;
        border: 1px solid #90CAF9;
        border-radius: var(--radius-lg);
        padding: 16px 20px;
        margin-bottom: 20px;
    }

    .revision-notice h4 {
        font-size: 0.85rem;
        font-weight: 700;
        color: #0277BD;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .revision-notice p {
        font-size: 0.85rem;
        color: #01579B;
    }

    /* Form card */
    .form-card {
        background: var(--bg-card);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: var(--shadow-md);
    }

    .form-card-header {
        background: linear-gradient(140deg, #29B6F6 0%, #0277BD 100%);
        padding: 24px 28px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .form-card-header .fch-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-lg);
        background: rgba(255,255,255,0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .form-card-header h2 {
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .form-card-header .fch-sub {
        font-size: 0.82rem;
        opacity: 0.7;
    }

    /* Aset summary */
    .aset-summary {
        padding: 20px 28px;
        background: var(--bg-surface-variant);
        border-bottom: 1px solid var(--border);
    }

    .aset-summary-top {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .aset-summary .as-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-md);
        background: #E3F2FD;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: #1565C0;
        flex-shrink: 0;
    }

    .aset-summary .as-name {
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .aset-summary .as-kode {
        font-size: 0.78rem;
        color: var(--text-tertiary);
    }

    /* Form body */
    .form-body { padding: 28px; }

    .form-group { margin-bottom: 24px; }
    .form-group:last-child { margin-bottom: 0; }

    .form-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .form-label .required { color: #C62828; }

    .form-sublabel {
        font-size: 0.78rem;
        color: var(--text-tertiary);
        margin-bottom: 10px;
    }

    .form-input, .form-select {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 12px 16px;
        font-size: 0.88rem;
        color: var(--text-primary);
        background: var(--bg-card);
        font-family: inherit;
        transition: border-color var(--transition);
    }

    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: #0277BD;
        box-shadow: 0 0 0 3px rgba(2, 119, 189, 0.1);
    }

    .form-actions {
        padding: 20px 28px;
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .btn-submit {
        background: linear-gradient(140deg, #29B6F6 0%, #0277BD 100%);
        color: #fff;
        border: none;
        padding: 12px 32px;
        border-radius: var(--radius-full);
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        transition: all var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-submit:hover { box-shadow: var(--shadow-lg); transform: translateY(-1px); }

    .btn-cancel {
        background: none;
        border: 1px solid var(--border);
        padding: 12px 24px;
        border-radius: var(--radius-full);
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-decoration: none;
        transition: all var(--transition);
    }

    .btn-cancel:hover { border-color: var(--text-tertiary); color: var(--text-primary); }

    .form-error {
        font-size: 0.78rem;
        color: #C62828;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .existing-file {
        font-size: 0.78rem;
        color: var(--text-tertiary);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .existing-file i { color: #2E7D32; }

    @media (max-width: 768px) {
        .form-body { padding: 20px; }
        .form-actions { padding: 16px 20px; flex-direction: column; align-items: stretch; }
        .btn-submit, .btn-cancel { justify-content: center; text-align: center; }
    }
</style>
@endsection

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 100px 20px 40px;">
    <a href="{{ route('sewa-reklame.detail', $permohonan->nomor_tiket) }}" class="obj-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Detail Permohonan
    </a>

    {{-- Revision notice --}}
    @if($permohonan->catatan_petugas)
        <div class="revision-notice">
            <h4><i class="bi bi-info-circle-fill"></i> Catatan Petugas</h4>
            <p>{{ $permohonan->catatan_petugas }}</p>
        </div>
    @endif

    @session('error')
        <div style="background: #FFEBEE; border: 1px solid #EF9A9A; border-radius: var(--radius-lg); padding: 14px 20px; margin-bottom: 20px; font-size: 0.85rem; color: #C62828; display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ $value }}
        </div>
    @endsession

    <div class="form-card">
        <div class="form-card-header">
            <div class="fch-icon"><i class="bi bi-pencil-square"></i></div>
            <div>
                <h2>Revisi Permohonan — {{ $permohonan->nomor_tiket }}</h2>
                <div class="fch-sub">Perbaiki data sesuai catatan petugas, lalu kirim ulang</div>
            </div>
        </div>

        {{-- Aset Summary --}}
        @if($permohonan->asetReklame)
            <div class="aset-summary">
                <div class="aset-summary-top">
                    <div class="as-icon">
                        <i class="bi bi-{{ $permohonan->asetReklame->jenis === 'neon_box' ? 'lamp' : 'signpost-2-fill' }}"></i>
                    </div>
                    <div>
                        <div class="as-name">{{ $permohonan->asetReklame->nama }}</div>
                        <div class="as-kode">{{ $permohonan->asetReklame->kode_aset }} &bull; {{ $permohonan->asetReklame->lokasi }}</div>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('sewa-reklame.update', $permohonan->nomor_tiket) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-body">
                {{-- Email --}}
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="form-sublabel">Opsional, untuk notifikasi status</div>
                    <input type="email" class="form-input" name="email" id="email"
                           value="{{ old('email', $permohonan->email) }}"
                           placeholder="contoh@email.com" style="max-width: 400px;">
                    @error('email')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- NPWPD --}}
                <div class="form-group">
                    <label class="form-label" for="npwpd">NPWPD</label>
                    <div class="form-sublabel">Opsional. Jika Anda sudah terdaftar sebagai wajib pajak, masukkan NPWPD untuk mempercepat proses.</div>
                    <div style="display: flex; gap: 8px; max-width: 400px;">
                        <input type="text" class="form-input" name="npwpd" id="npwpd"
                               value="{{ old('npwpd', $permohonan->npwpd) }}"
                               placeholder="Masukkan NPWPD" maxlength="13" style="flex: 1;">
                        <button type="button" onclick="cariNpwpd()" style="background: #0277BD; color: #fff; border: none; padding: 10px 18px; border-radius: var(--radius-md); font-size: 0.82rem; font-weight: 700; cursor: pointer; white-space: nowrap;">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                    <div id="npwpd-result" style="display: none; margin-top: 8px; font-size: 0.82rem; padding: 8px 12px; border-radius: var(--radius-md);"></div>
                    @error('npwpd')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Nomor Registrasi Izin DPMPTSP --}}
                <div class="form-group">
                    <label class="form-label" for="nomor_registrasi_izin">Nomor Registrasi Izin DPMPTSP <span class="required">*</span></label>
                    <div class="form-sublabel">Nomor registrasi izin dari DPMPTSP Kab Bojonegoro</div>
                    <input type="text" class="form-input" name="nomor_registrasi_izin" id="nomor_registrasi_izin"
                           value="{{ old('nomor_registrasi_izin', $permohonan->nomor_registrasi_izin) }}"
                           placeholder="Masukkan nomor registrasi izin" style="max-width: 400px;">
                    @error('nomor_registrasi_izin')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Jenis Reklame --}}
                <div class="form-group">
                    <label class="form-label" for="jenis_reklame">Jenis Reklame yang Dipasang <span class="required">*</span></label>
                    <input type="text" class="form-input" name="jenis_reklame_dipasang" id="jenis_reklame"
                           value="{{ old('jenis_reklame_dipasang', $permohonan->jenis_reklame_dipasang) }}"
                           placeholder="Contoh: Promosi produk makanan">
                    @error('jenis_reklame_dipasang')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Durasi --}}
                <div class="form-group">
                    <label class="form-label">Durasi Sewa <span class="required">*</span></label>
                    @php
                        $editSatuan = $permohonan->satuan_sewa;
                        if (!$editSatuan) {
                            $oldDurasiHari = $permohonan->durasi_sewa_hari;
                            if ($oldDurasiHari >= 365 && $oldDurasiHari % 365 === 0) {
                                $editSatuan = 'tahun'; $editJumlah = $oldDurasiHari / 365;
                            } elseif ($oldDurasiHari >= 28 && $oldDurasiHari % 30 === 0) {
                                $editSatuan = 'bulan'; $editJumlah = $oldDurasiHari / 30;
                            } else {
                                $editSatuan = 'minggu'; $editJumlah = max(1, round($oldDurasiHari / 7));
                            }
                        } else {
                            $editJumlah = match ($editSatuan) {
                                'tahun'  => $permohonan->durasi_sewa_hari / 365,
                                'bulan'  => $permohonan->durasi_sewa_hari / 30,
                                'minggu' => $permohonan->durasi_sewa_hari / 7,
                                default  => $permohonan->durasi_sewa_hari,
                            };
                            $editJumlah = max(1, (int) round($editJumlah));
                        }
                    @endphp
                    <div style="display: flex; gap: 12px; max-width: 500px; align-items: flex-end;">
                        <div style="flex: 1;">
                            <label class="form-label" style="font-size: 0.8rem;">Jumlah</label>
                            <input type="number" class="form-input" name="jumlah_sewa" id="jumlah_sewa"
                                   value="{{ old('jumlah_sewa', $editJumlah) }}"
                                   min="1" max="11">
                        </div>
                        <div style="flex: 1.5;">
                            <label class="form-label" style="font-size: 0.8rem;">Satuan</label>
                            <select class="form-select" name="satuan_sewa" id="satuan_sewa">
                                <option value="minggu" @selected(old('satuan_sewa', $editSatuan) === 'minggu')>Minggu</option>
                                <option value="bulan" @selected(old('satuan_sewa', $editSatuan) === 'bulan')>Bulan</option>
                                <option value="tahun" @selected(old('satuan_sewa', $editSatuan) === 'tahun')>Tahun</option>
                            </select>
                        </div>
                    </div>
                    <div id="durasi-hint" style="display: none; margin-top: 8px; font-size: 0.78rem; padding: 8px 12px; border-radius: var(--radius-md); background: #FFF3E0; color: #E65100; align-items: center; gap: 4px;">
                        <i class="bi bi-info-circle"></i> <span id="durasi-hint-text"></span>
                    </div>
                    @error('jumlah_sewa')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                    @error('satuan_sewa')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Tanggal Mulai --}}
                <div class="form-group">
                    <label class="form-label" for="tanggal_mulai">Tanggal Mulai Diinginkan <span class="required">*</span></label>
                    <input type="date" class="form-input" name="tanggal_mulai_diinginkan" id="tanggal_mulai"
                           value="{{ old('tanggal_mulai_diinginkan', $permohonan->tanggal_mulai_diinginkan?->format('Y-m-d')) }}"
                           min="{{ date('Y-m-d') }}" style="max-width: 300px;">
                    @error('tanggal_mulai_diinginkan')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Upload KTP --}}
                <div class="form-group">
                    <label class="form-label" for="file_ktp">Upload KTP</label>
                    @if($permohonan->file_ktp)
                        <div class="existing-file"><i class="bi bi-check-circle-fill"></i> File KTP sudah terupload. Upload ulang untuk mengganti.</div>
                    @endif
                    <input type="file" class="form-input" name="file_ktp" id="file_ktp" accept=".jpg,.jpeg,.png,.pdf">
                    <div class="form-sublabel" style="margin-top: 4px;">Format: JPG, PNG, atau PDF. Maksimal 2MB</div>
                    @error('file_ktp')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Upload NPWP --}}
                <div class="form-group">
                    <label class="form-label" for="file_npwp">Upload NPWP</label>
                    @if($permohonan->file_npwp)
                        <div class="existing-file"><i class="bi bi-check-circle-fill"></i> File NPWP sudah terupload. Upload ulang untuk mengganti.</div>
                    @endif
                    <input type="file" class="form-input" name="file_npwp" id="file_npwp" accept=".jpg,.jpeg,.png,.pdf">
                    <div class="form-sublabel" style="margin-top: 4px;">Opsional. Format: JPG, PNG, atau PDF. Maksimal 2MB</div>
                    @error('file_npwp')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Upload Desain --}}
                <div class="form-group">
                    <label class="form-label" for="file_desain_reklame">Upload Desain / Materi Reklame</label>
                    @if($permohonan->file_desain_reklame)
                        <div class="existing-file"><i class="bi bi-check-circle-fill"></i> File desain sudah terupload. Upload ulang untuk mengganti.</div>
                    @endif
                    <input type="file" class="form-input" name="file_desain_reklame" id="file_desain_reklame" accept=".jpg,.jpeg,.png,.pdf">
                    <div class="form-sublabel" style="margin-top: 4px;">Format: JPG, PNG, atau PDF. Maksimal 5MB</div>
                    @error('file_desain_reklame')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Catatan --}}
                <div class="form-group">
                    <label class="form-label" for="catatan">Catatan Tambahan</label>
                    <textarea class="form-input" name="catatan" id="catatan" rows="3" placeholder="Catatan untuk petugas...">{{ old('catatan', $permohonan->catatan) }}</textarea>
                    @error('catatan')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-send"></i>
                    Kirim Ulang Permohonan
                </button>
                <a href="{{ route('sewa-reklame.detail', $permohonan->nomor_tiket) }}" class="btn-cancel">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ── Auto-compress image files to max 1MB ────────────────
    const MAX_SIZE_BYTES = 1 * 1024 * 1024; // 1MB

    function compressImage(file, maxSize) {
        return new Promise((resolve) => {
            if (!file.type.match(/^image\/(jpeg|png|jpg)$/)) {
                resolve(file);
                return;
            }
            if (file.size <= maxSize) {
                resolve(file);
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;

                    const maxDim = 2048;
                    if (width > maxDim || height > maxDim) {
                        const ratio = Math.min(maxDim / width, maxDim / height);
                        width = Math.round(width * ratio);
                        height = Math.round(height * ratio);
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    let quality = 0.8;
                    (function tryCompress() {
                        canvas.toBlob(function(blob) {
                            if (blob.size > maxSize && quality > 0.1) {
                                quality -= 0.1;
                                tryCompress();
                            } else {
                                const compressed = new File([blob], file.name, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now(),
                                });
                                resolve(compressed);
                            }
                        }, 'image/jpeg', quality);
                    })();
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    document.querySelectorAll('input[type="file"]').forEach(function(input) {
        input.addEventListener('change', async function() {
            if (!this.files || !this.files[0]) return;
            const original = this.files[0];
            if (!original.type.match(/^image\/(jpeg|png|jpg)$/)) return;
            if (original.size <= MAX_SIZE_BYTES) return;

            const compressed = await compressImage(original, MAX_SIZE_BYTES);
            const dt = new DataTransfer();
            dt.items.add(compressed);
            this.files = dt.files;
        });
    });
</script>

<script>
    function cariNpwpd() {
        const npwpd = document.getElementById('npwpd').value.trim();
        const result = document.getElementById('npwpd-result');
        if (!npwpd) {
            result.style.display = 'block';
            result.style.background = '#FFF3E0';
            result.style.color = '#E65100';
            result.textContent = 'Masukkan NPWPD terlebih dahulu.';
            return;
        }
        result.style.display = 'block';
        result.style.background = '#E3F2FD';
        result.style.color = '#0277BD';
        result.textContent = 'Mencari...';
        fetch('/api/cek-npwpd/' + encodeURIComponent(npwpd))
            .then(function(r) {
                if (!r.ok) throw new Error('not found');
                return r.json();
            })
            .then(function(data) {
                result.style.background = '#E8F5E9';
                result.style.color = '#2E7D32';
                result.innerHTML = '<i class="bi bi-check-circle-fill"></i> NPWPD ditemukan: <strong>' + data.nama + '</strong>';
            })
            .catch(function() {
                result.style.background = '#FFF3E0';
                result.style.color = '#E65100';
                result.innerHTML = '<i class="bi bi-exclamation-circle"></i> NPWPD tidak ditemukan. Biarkan kosong jika belum memiliki.';
            });
    }
</script>

<script>
    // ── Durasi limit enforcement ────────────────────────────
    (function() {
        const jumlahInput = document.getElementById('jumlah_sewa');
        const satuanSelect = document.getElementById('satuan_sewa');
        const hint = document.getElementById('durasi-hint');
        const hintText = document.getElementById('durasi-hint-text');

        const maxPerSatuan = { minggu: 3, bulan: 11, tahun: 100 };
        const saranSatuan = { minggu: 'bulan', bulan: 'tahun' };
        const labelSatuan = { minggu: 'minggu', bulan: 'bulan', tahun: 'tahun' };

        function enforce() {
            const satuan = satuanSelect.value;
            const maxVal = maxPerSatuan[satuan] || 100;
            jumlahInput.max = maxVal;

            const val = parseInt(jumlahInput.value) || 1;
            if (val > maxVal) {
                jumlahInput.value = maxVal;
            }

            if (saranSatuan[satuan]) {
                hint.style.display = 'flex';
                hintText.textContent = 'Maksimal ' + maxVal + ' ' + labelSatuan[satuan] + '. Untuk durasi lebih panjang, gunakan satuan ' + saranSatuan[satuan] + '.';
            } else {
                hint.style.display = 'none';
            }
        }

        satuanSelect.addEventListener('change', enforce);
        jumlahInput.addEventListener('input', function() {
            const satuan = satuanSelect.value;
            const maxVal = maxPerSatuan[satuan] || 100;
            if (parseInt(this.value) > maxVal) {
                this.value = maxVal;
            }
        });

        enforce();
    })();
</script>
@endsection
