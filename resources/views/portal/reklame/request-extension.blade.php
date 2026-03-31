@extends('layouts.portal-dashboard')

@section('title', 'Perpanjangan Reklame - Borotax Portal')
@section('page-title', 'Ajukan Perpanjangan Reklame')

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
        background: linear-gradient(140deg, #FF7043 0%, #E64A19 100%);
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

    /* Object summary */
    .obj-summary {
        padding: 20px 28px;
        background: var(--bg-surface-variant);
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .obj-summary .os-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-md);
        background: #FBE9E7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: #BF360C;
        flex-shrink: 0;
    }

    .obj-summary .os-name {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1px;
    }

    .obj-summary .os-addr {
        font-size: 0.78rem;
        color: var(--text-tertiary);
    }

    .obj-summary .os-expiry {
        margin-left: auto;
        font-size: 0.78rem;
        font-weight: 600;
        color: #C62828;
        display: flex;
        align-items: center;
        gap: 4px;
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

    /* Duration options */
    .duration-options {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    .duration-option {
        position: relative;
    }

    .duration-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .duration-option label {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 16px 12px;
        background: var(--bg-surface-variant);
        border: 2px solid var(--border);
        border-radius: var(--radius-lg);
        cursor: pointer;
        transition: all var(--transition);
    }

    .duration-option label:hover {
        border-color: var(--primary-light);
    }

    .duration-option input:checked + label {
        border-color: #E64A19;
        background: #FBE9E7;
    }

    .duration-option .do-value {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
        margin-bottom: 4px;
    }

    .duration-option input:checked + label .do-value { color: #E64A19; }

    .duration-option .do-label {
        font-size: 0.75rem;
        color: var(--text-tertiary);
    }

    /* Textarea */
    .form-textarea {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 12px 16px;
        font-size: 0.88rem;
        color: var(--text-primary);
        background: var(--bg-card);
        resize: vertical;
        min-height: 100px;
        font-family: inherit;
        transition: border-color var(--transition);
    }

    .form-textarea:focus {
        outline: none;
        border-color: #E64A19;
        box-shadow: 0 0 0 3px rgba(230, 74, 25, 0.1);
    }

    .form-textarea::placeholder { color: var(--text-tertiary); }

    .char-count {
        font-size: 0.72rem;
        color: var(--text-tertiary);
        text-align: right;
        margin-top: 4px;
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
        background: linear-gradient(140deg, #FF7043 0%, #E64A19 100%);
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

    @media (max-width: 768px) {
        .duration-options { grid-template-columns: repeat(2, 1fr); }
        .form-body { padding: 20px; }
        .form-actions { padding: 16px 20px; flex-direction: column; align-items: stretch; }
        .btn-submit, .btn-cancel { justify-content: center; text-align: center; }
        .obj-summary { flex-wrap: wrap; }
        .obj-summary .os-expiry { margin-left: 0; }
    }
</style>
@endsection

@section('content')
    <a href="{{ route('portal.reklame.object-detail', $object->id) }}" class="obj-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Detail Objek
    </a>

    <div class="form-card">
        {{-- Header --}}
        <div class="form-card-header">
            <div class="fch-icon"><i class="bi bi-arrow-repeat"></i></div>
            <div>
                <h2>Ajukan Perpanjangan Reklame</h2>
                <div class="fch-sub">Perpanjang masa berlaku objek reklame Anda</div>
            </div>
        </div>

        {{-- Object Summary --}}
        <div class="obj-summary">
            <div class="os-icon"><i class="bi bi-signpost-2-fill"></i></div>
            <div>
                <div class="os-name">{{ $object->nama_reklame }}</div>
                <div class="os-addr">{{ $object->alamat_reklame }}, {{ $object->kelurahan }}</div>
            </div>
            <div class="os-expiry">
                <i class="bi bi-calendar-x"></i>
                @if($object->isKadaluarsa())
                    Kadaluarsa sejak {{ $object->masa_berlaku_sampai?->translatedFormat('d M Y') }}
                @else
                    Berlaku s/d {{ $object->masa_berlaku_sampai?->translatedFormat('d M Y') }} ({{ $object->sisa_hari }} hari)
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('portal.reklame.store-extension', $object->id) }}">
            @csrf

            <div class="form-body">
                {{-- Duration --}}
                <div class="form-group">
                    <label class="form-label">Durasi Perpanjangan <span class="required">*</span></label>
                    <div class="form-sublabel">Pilih durasi perpanjangan masa berlaku reklame</div>
                    <div class="duration-options">
                        <div class="duration-option">
                            <input type="radio" name="durasi_perpanjangan_hari" value="30" id="dur30"
                                   @checked(old('durasi_perpanjangan_hari') == '30')>
                            <label for="dur30">
                                <span class="do-value">1</span>
                                <span class="do-label">Bulan</span>
                            </label>
                        </div>
                        <div class="duration-option">
                            <input type="radio" name="durasi_perpanjangan_hari" value="90" id="dur90"
                                   @checked(old('durasi_perpanjangan_hari') == '90')>
                            <label for="dur90">
                                <span class="do-value">3</span>
                                <span class="do-label">Bulan</span>
                            </label>
                        </div>
                        <div class="duration-option">
                            <input type="radio" name="durasi_perpanjangan_hari" value="180" id="dur180"
                                   @checked(old('durasi_perpanjangan_hari') == '180')>
                            <label for="dur180">
                                <span class="do-value">6</span>
                                <span class="do-label">Bulan</span>
                            </label>
                        </div>
                        <div class="duration-option">
                            <input type="radio" name="durasi_perpanjangan_hari" value="365" id="dur365"
                                   @checked(old('durasi_perpanjangan_hari', '365') == '365')>
                            <label for="dur365">
                                <span class="do-value">1</span>
                                <span class="do-label">Tahun</span>
                            </label>
                        </div>
                    </div>
                    @error('durasi_perpanjangan_hari')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Notes --}}
                <div class="form-group">
                    <label class="form-label" for="catatan">Catatan (Opsional)</label>
                    <div class="form-sublabel">Tambahkan catatan atau informasi tambahan untuk petugas</div>
                    <textarea class="form-textarea" name="catatan_pengajuan" id="catatan"
                              placeholder="Contoh: Reklame masih digunakan untuk promosi toko..."
                              maxlength="500"
                              oninput="document.getElementById('charCount').textContent = this.value.length + '/500'">{{ old('catatan_pengajuan') }}</textarea>
                    <div class="char-count" id="charCount">0/500</div>
                    @error('catatan_pengajuan')
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-send"></i>
                    Kirim Pengajuan
                </button>
                <a href="{{ route('portal.reklame.object-detail', $object->id) }}" class="btn-cancel">Batal</a>
            </div>
        </form>
    </div>
@endsection
