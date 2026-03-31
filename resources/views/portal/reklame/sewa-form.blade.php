@extends('layouts.portal-guest')

@section('title', 'Ajukan Sewa Reklame - Borotax Portal')

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

    /* Form card */
    .form-card {
        background: var(--bg-card);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: var(--shadow-md);
    }

    .form-card-header {
        background: linear-gradient(140deg, #42A5F5 0%, #1565C0 100%);
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
        margin-bottom: 14px;
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

    .aset-detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
    }

    .aset-detail-item {
        font-size: 0.78rem;
    }

    .aset-detail-item .adi-label {
        color: var(--text-tertiary);
        margin-bottom: 2px;
    }

    .aset-detail-item .adi-value {
        font-weight: 600;
        color: var(--text-primary);
    }

    .aset-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: var(--radius-full);
        background: #E8F5E9;
        color: #2E7D32;
    }

    /* Form body */
    .form-body {
        padding: 28px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group:last-child { margin-bottom: 0; }

    .form-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .form-label .required {
        color: #C62828;
    }

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
        border-color: #1565C0;
        box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.1);
    }

    /* Submit */
    .form-actions {
        padding: 20px 28px;
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .btn-submit {
        background: linear-gradient(140deg, #42A5F5 0%, #1565C0 100%);
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

    /* Validation errors */
    .form-error {
        font-size: 0.78rem;
        color: #C62828;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Pricing info */
    .pricing-info {
        background: #E3F2FD;
        border: 1px solid #BBDEFB;
        border-radius: var(--radius-lg);
        padding: 16px 20px;
        margin-bottom: 24px;
    }

    .pricing-info h4 {
        font-size: 0.82rem;
        font-weight: 700;
        color: #1565C0;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 8px;
    }

    .pricing-item {
        font-size: 0.78rem;
    }

    .pricing-item .pi-label {
        color: #1565C0;
        opacity: 0.7;
    }

    .pricing-item .pi-value {
        font-weight: 700;
        color: #0D47A1;
    }

    @media (max-width: 768px) {
        .duration-options { grid-template-columns: repeat(2, 1fr); }
        .form-body { padding: 20px; }
        .form-actions { padding: 16px 20px; flex-direction: column; align-items: stretch; }
        .btn-submit, .btn-cancel { justify-content: center; text-align: center; }
        .aset-detail-grid { grid-template-columns: 1fr 1fr; }
    }
</style>
@endsection

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 100px 20px 40px;">
    <a href="{{ route('publik.sewa-reklame') }}" class="obj-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Aset
    </a>

    @session('error')
        <div style="background: #FFEBEE; border: 1px solid #EF9A9A; border-radius: var(--radius-lg); padding: 14px 20px; margin-bottom: 20px; font-size: 0.85rem; color: #C62828; display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ $value }}
        </div>
    @endsession

    <div class="form-card">
        {{-- Header --}}
        <div class="form-card-header">
            <div class="fch-icon"><i class="bi bi-clipboard-plus"></i></div>
            <div>
                <h2>Ajukan Sewa Reklame</h2>
                <div class="fch-sub">Isi formulir untuk mengajukan sewa titik reklame milik Pemkab Bojonegoro</div>
            </div>
        </div>

        {{-- Aset Summary --}}
        <div class="aset-summary">
            <div class="aset-summary-top">
                <div class="as-icon">
                    <i class="bi bi-{{ $aset->jenis === 'neon_box' ? 'lamp' : 'signpost-2-fill' }}"></i>
                </div>
                <div>
                    <div class="as-name">{{ $aset->nama }}</div>
                    <div class="as-kode">{{ $aset->kode_aset }} &bull; {{ ucfirst(str_replace('_', ' ', $aset->jenis)) }}</div>
                </div>
                <div style="margin-left: auto;">
                    <span class="aset-status-badge"><i class="bi bi-check-circle"></i> Tersedia</span>
                </div>
            </div>
            <div class="aset-detail-grid">
                <div class="aset-detail-item">
                    <div class="adi-label">Lokasi</div>
                    <div class="adi-value">{{ $aset->lokasi }}</div>
                </div>
                <div class="aset-detail-item">
                    <div class="adi-label">Ukuran</div>
                    <div class="adi-value">{{ $aset->panjang }}m × {{ $aset->lebar }}m ({{ $aset->luas_m2 }} m²)</div>
                </div>
                <div class="aset-detail-item">
                    <div class="adi-label">Jumlah Muka</div>
                    <div class="adi-value">{{ $aset->jumlah_muka }} muka</div>
                </div>
                <div class="aset-detail-item">
                    <div class="adi-label">Kawasan</div>
                    <div class="adi-value">{{ ucfirst($aset->kawasan ?? '-') }}</div>
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <div style="padding: 20px 28px 0;">
            <div class="pricing-info">
                <h4><i class="bi bi-tag"></i> Harga Sewa</h4>
                <div class="pricing-grid">
                    @if($aset->harga_sewa_per_tahun)
                        <div class="pricing-item">
                            <div class="pi-label">Per Tahun</div>
                            <div class="pi-value">Rp {{ number_format($aset->harga_sewa_per_tahun, 0, ',', '.') }}</div>
                        </div>
                    @endif
                    @if($aset->harga_sewa_per_bulan)
                        <div class="pricing-item">
                            <div class="pi-label">Per Bulan</div>
                            <div class="pi-value">Rp {{ number_format($aset->harga_sewa_per_bulan, 0, ',', '.') }}</div>
                        </div>
                    @endif
                    @if($aset->harga_sewa_per_minggu)
                        <div class="pricing-item">
                            <div class="pi-label">Per Minggu</div>
                            <div class="pi-value">Rp {{ number_format($aset->harga_sewa_per_minggu, 0, ',', '.') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('sewa-reklame.store', $aset->id) }}" enctype="multipart/form-data">
            @csrf

            <div class="form-body">
                {{-- Data Pemohon --}}
                <div style="margin-bottom: 8px; padding-bottom: 16px; border-bottom: 1px solid var(--border);">
                    <h3 style="font-size: 0.92rem; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-person-fill"></i> Data Pemohon
                    </h3>

                    <div class="form-group">
                        <label class="form-label" for="nik">NIK <span class="required">*</span></label>
                        <input type="text" class="form-input" name="nik" id="nik"
                               value="{{ old('nik') }}"
                               placeholder="Masukkan 16 digit NIK" maxlength="20" style="max-width: 400px;">
                        @error('nik')
                            <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nama">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" class="form-input" name="nama" id="nama"
                               value="{{ old('nama') }}"
                               placeholder="Masukkan nama lengkap sesuai KTP">
                        @error('nama')
                            <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="alamat">Alamat <span class="required">*</span></label>
                        <textarea class="form-input" name="alamat" id="alamat" rows="2" placeholder="Masukkan alamat lengkap">{{ old('alamat') }}</textarea>
                        @error('alamat')
                            <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="no_telepon">No. Telepon / HP <span class="required">*</span></label>
                        <input type="text" class="form-input" name="no_telepon" id="no_telepon"
                               value="{{ old('no_telepon') }}"
                               placeholder="Contoh: 08123456789" style="max-width: 400px;">
                        @error('no_telepon')
                            <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nama_usaha">Nama Usaha / Badan</label>
                        <div class="form-sublabel">Opsional, isi jika atas nama badan usaha</div>
                        <input type="text" class="form-input" name="nama_usaha" id="nama_usaha"
                               value="{{ old('nama_usaha') }}"
                               placeholder="Nama perusahaan / badan usaha">
                        @error('nama_usaha')
                            <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <div class="form-sublabel">Opsional, untuk notifikasi status permohonan</div>
                        <input type="email" class="form-input" name="email" id="email"
                               value="{{ old('email') }}"
                               placeholder="contoh@email.com" style="max-width: 400px;">
                    @error('email')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="npwpd">NPWPD</label>
                        <div class="form-sublabel">Opsional. Jika Anda sudah terdaftar sebagai wajib pajak, masukkan NPWPD untuk mempercepat proses.</div>
                        <div style="display: flex; gap: 8px; max-width: 400px;">
                            <input type="text" class="form-input" name="npwpd" id="npwpd"
                                   value="{{ old('npwpd') }}"
                                   placeholder="Masukkan NPWPD" maxlength="13" style="flex: 1;">
                            <button type="button" onclick="cariNpwpd()" style="background: #1565C0; color: #fff; border: none; padding: 10px 18px; border-radius: var(--radius-md); font-size: 0.82rem; font-weight: 700; cursor: pointer; white-space: nowrap;">
                                <i class="bi bi-search"></i> Cari
                            </button>
                        </div>
                        <div id="npwpd-result" style="display: none; margin-top: 8px; font-size: 0.82rem; padding: 8px 12px; border-radius: var(--radius-md);"></div>
                        @error('npwpd')
                            <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Detail Sewa --}}
                <div style="margin-bottom: 8px; padding-bottom: 16px; border-bottom: 1px solid var(--border);">
                    <h3 style="font-size: 0.92rem; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-file-earmark-text-fill"></i> Detail Sewa
                    </h3>

                {{-- Nomor Registrasi Izin DPMPTSP --}}
                <div class="form-group">
                    <label class="form-label" for="nomor_registrasi_izin">Nomor Registrasi Izin DPMPTSP <span class="required">*</span></label>
                    <div class="form-sublabel">Nomor registrasi izin dari DPMPTSP Kab Bojonegoro</div>
                    <input type="text" class="form-input" name="nomor_registrasi_izin" id="nomor_registrasi_izin"
                           value="{{ old('nomor_registrasi_izin') }}"
                           placeholder="Masukkan nomor registrasi izin" style="max-width: 400px;">
                    @error('nomor_registrasi_izin')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Jenis Reklame --}}
                <div class="form-group">
                    <label class="form-label" for="jenis_reklame">Jenis Reklame yang Dipasang <span class="required">*</span></label>
                    <div class="form-sublabel">Jelaskan jenis/konten reklame yang akan dipasang pada titik ini</div>
                    <input type="text" class="form-input" name="jenis_reklame_dipasang" id="jenis_reklame"
                           value="{{ old('jenis_reklame_dipasang') }}"
                           placeholder="Contoh: Promosi produk makanan, Spanduk event, dll">
                    @error('jenis_reklame_dipasang')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Durasi --}}
                <div class="form-group">
                    <label class="form-label">Durasi Sewa <span class="required">*</span></label>
                    <div class="form-sublabel">Pilih satuan waktu dan jumlah durasi sewa</div>
                    <div style="display: flex; gap: 12px; max-width: 500px; align-items: flex-end;">
                        <div style="flex: 1;">
                            <label class="form-label" style="font-size: 0.8rem;">Jumlah</label>
                            <input type="number" class="form-input" name="jumlah_sewa" id="jumlah_sewa"
                                   value="{{ old('jumlah_sewa', 1) }}"
                                   min="1" max="11" placeholder="Jumlah">
                        </div>
                        <div style="flex: 1.5;">
                            <label class="form-label" style="font-size: 0.8rem;">Satuan</label>
                            <select class="form-select" name="satuan_sewa" id="satuan_sewa">
                                <option value="minggu" @selected(old('satuan_sewa') === 'minggu')>Minggu</option>
                                <option value="bulan" @selected(old('satuan_sewa', 'bulan') === 'bulan')>Bulan</option>
                                <option value="tahun" @selected(old('satuan_sewa') === 'tahun')>Tahun</option>
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
                    <div class="form-sublabel">Tanggal dari mana Anda ingin memulai sewa</div>
                    <input type="date" class="form-input" name="tanggal_mulai_diinginkan" id="tanggal_mulai"
                           value="{{ old('tanggal_mulai_diinginkan', date('Y-m-d')) }}"
                           min="{{ date('Y-m-d') }}" style="max-width: 300px;">
                    @error('tanggal_mulai_diinginkan')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>
                </div>

                {{-- Dokumen --}}
                <div style="margin-bottom: 8px;">
                    <h3 style="font-size: 0.92rem; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-paperclip"></i> Dokumen Pendukung
                    </h3>

                {{-- Upload KTP --}}
                <div class="form-group">
                    <label class="form-label" for="file_ktp">Upload KTP <span class="required">*</span></label>
                    <div class="form-sublabel">Format: JPG, PNG, atau PDF. Maksimal 2MB</div>
                    <input type="file" class="form-input" name="file_ktp" id="file_ktp" accept=".jpg,.jpeg,.png,.pdf">
                    @error('file_ktp')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Upload NPWP --}}
                <div class="form-group">
                    <label class="form-label" for="file_npwp">Upload NPWP</label>
                    <div class="form-sublabel">Opsional, wajib untuk badan usaha. Format: JPG, PNG, atau PDF. Maksimal 2MB</div>
                    <input type="file" class="form-input" name="file_npwp" id="file_npwp" accept=".jpg,.jpeg,.png,.pdf">
                    @error('file_npwp')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Upload Desain Reklame --}}
                <div class="form-group">
                    <label class="form-label" for="file_desain_reklame">Upload Desain / Materi Reklame <span class="required">*</span></label>
                    <div class="form-sublabel">Desain reklame yang akan dipasang. Format: JPG, PNG, atau PDF. Maksimal 5MB</div>
                    <input type="file" class="form-input" name="file_desain_reklame" id="file_desain_reklame" accept=".jpg,.jpeg,.png,.pdf">
                    @error('file_desain_reklame')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>
                </div>

                {{-- Catatan --}}
                <div class="form-group">
                    <label class="form-label" for="catatan">Catatan Tambahan</label>
                    <div class="form-sublabel">Informasi tambahan terkait permohonan (opsional)</div>
                    <textarea class="form-input" name="catatan" id="catatan" rows="3" placeholder="Catatan untuk petugas...">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-send"></i>
                    Kirim Permohonan
                </button>
                <a href="{{ route('publik.sewa-reklame') }}" class="btn-cancel">Batal</a>
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
                resolve(file); // skip non-image (PDF)
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

                    // Scale down if very large
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
        result.style.color = '#1565C0';
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

            // Show hint
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
