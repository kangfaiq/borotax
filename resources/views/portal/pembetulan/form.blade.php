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

    .file-upload-area {
        border: 2px dashed var(--border);
        border-radius: var(--radius-md);
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: all var(--transition);
        position: relative;
        background: var(--bg-card);
    }

    .file-upload-area:hover {
        border-color: var(--primary);
        background: var(--primary-50);
    }

    .file-upload-area.is-dragover {
        border-color: var(--primary);
        background: var(--primary-50);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.12);
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

    .file-selected-meta {
        flex: 1;
        min-width: 0;
    }

    .file-selected-meta span,
    .file-selected-meta small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .file-selected-meta span {
        font-size: 0.82rem;
        color: var(--text-primary);
        font-weight: 500;
    }

    .file-selected-meta small {
        margin-top: 2px;
        font-size: 0.74rem;
        color: var(--text-secondary);
    }

    .file-processing {
        margin-top: 10px;
        font-size: 0.78rem;
        color: var(--primary-dark);
    }

    .file-preview-card {
        margin-top: 12px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
        background: var(--bg-surface);
        padding: 14px;
    }

    .file-preview-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .file-preview-head span:first-child {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .file-preview-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: var(--radius-full);
        background: var(--primary-50);
        color: var(--primary-dark);
        font-size: 0.72rem;
        font-weight: 700;
    }

    .file-preview-card img,
    .file-preview-card iframe {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: calc(var(--radius-md) - 4px);
        background: #fff;
    }

    .file-preview-card img {
        max-height: 320px;
        object-fit: contain;
    }

    .file-preview-card iframe {
        min-height: 360px;
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

        .file-preview-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .file-preview-card iframe {
            min-height: 280px;
        }
    }
</style>
@endsection

@section('content')
    <a href="{{ route('portal.pembetulan.index') }}" class="pemb-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Billing
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
            $displayStatus = $tax->display_status;

            $statusClass = match($displayStatus) {
                App\Enums\TaxStatus::Paid => 'paid',
                App\Enums\TaxStatus::Verified => 'verified',
                App\Enums\TaxStatus::Expired => 'rejected',
                default => 'pending',
            };
            $statusLabel = match($displayStatus) {
                App\Enums\TaxStatus::Paid => 'Sudah Dibayar',
                App\Enums\TaxStatus::Verified => 'Terverifikasi',
                App\Enums\TaxStatus::Expired => 'Kedaluwarsa',
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
    <form method="POST" action="{{ route('portal.pembetulan.store') }}" enctype="multipart/form-data" id="pembetulanForm">
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
                <div class="file-upload-area" id="pembetulanFileUploadArea" data-max-file-size="{{ 1024 * 1024 }}" data-auto-compress-images="true">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <p><strong>Klik untuk upload</strong> atau drag & drop</p>
                    <p style="font-size:0.72rem; margin-top:4px;">JPG, PNG, PDF &bull; Maks 1MB &bull; Gambar di atas 1MB dikompres otomatis sebelum upload</p>
                    <input type="file" class="form-control" name="lampiran" id="inputLampiranPembetulan" accept=".jpg,.jpeg,.png,.pdf" data-max-file-size="{{ 1024 * 1024 }}" data-auto-compress-images="true">
                </div>
                <div class="file-selected" id="pembetulanFileSelected" style="display:none;">
                    <i class="bi bi-check-circle-fill"></i>
                    <div class="file-selected-meta">
                        <span id="pembetulanFileName">-</span>
                        <small id="pembetulanFileMeta">-</small>
                    </div>
                </div>
                <div class="file-processing" id="pembetulanFileProcessing" style="display:none;"></div>
                <div class="form-error" id="pembetulanAttachmentClientError" style="display:none;"></div>
                <div class="file-preview-card" id="pembetulanAttachmentPreviewCard" style="display:none;">
                    <div class="file-preview-head">
                        <span>Preview Dokumen</span>
                        <span class="file-preview-badge" id="pembetulanAttachmentPreviewBadge">-</span>
                    </div>
                    <img id="pembetulanAttachmentPreviewImage" alt="Preview dokumen lampiran pembetulan" style="display:none;">
                    <iframe id="pembetulanAttachmentPreviewPdf" title="Preview dokumen lampiran pembetulan" style="display:none;"></iframe>
                </div>
                <div class="form-hint">Maksimal 1MB. Format: JPG, PNG, atau PDF. Dokumen pendukung alasan pembetulan.</div>
                @error('lampiran')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn-submit" id="btnSubmitPembetulan">
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
        var pembetulanForm = document.getElementById('pembetulanForm');
        var btnSubmitPembetulan = document.getElementById('btnSubmitPembetulan');
        var fileUploadArea = document.getElementById('pembetulanFileUploadArea');
        var fileInput = document.getElementById('inputLampiranPembetulan');
        var fileSelected = document.getElementById('pembetulanFileSelected');
        var fileName = document.getElementById('pembetulanFileName');
        var fileMeta = document.getElementById('pembetulanFileMeta');
        var fileProcessing = document.getElementById('pembetulanFileProcessing');
        var attachmentClientError = document.getElementById('pembetulanAttachmentClientError');
        var attachmentPreviewCard = document.getElementById('pembetulanAttachmentPreviewCard');
        var attachmentPreviewBadge = document.getElementById('pembetulanAttachmentPreviewBadge');
        var attachmentPreviewImage = document.getElementById('pembetulanAttachmentPreviewImage');
        var attachmentPreviewPdf = document.getElementById('pembetulanAttachmentPreviewPdf');
        var maxAttachmentBytes = fileInput ? parseInt(fileInput.dataset.maxFileSize || '1048576', 10) : 1048576;
        var initialSubmitLabel = btnSubmitPembetulan ? btnSubmitPembetulan.innerHTML : '';
        var attachmentPreviewUrl = null;
        var attachmentIsProcessing = false;
        var resubmitAfterAttachmentProcessing = false;

        if (omzetInput) {
            omzetInput.addEventListener('input', function() {
                var raw = this.value.replace(/\D/g, '');
                this.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                omzetReal.value = raw ? parseInt(raw) : '';
            });
        }

        function formatFileSize(bytes) {
            if (bytes < 1024) {
                return bytes + ' B';
            }

            if (bytes < 1024 * 1024) {
                return (bytes / 1024).toFixed(0) + ' KB';
            }

            return (bytes / 1024 / 1024).toFixed(2) + ' MB';
        }

        function setSubmitProcessingState(isProcessing, label) {
            if (!btnSubmitPembetulan) {
                return;
            }

            btnSubmitPembetulan.disabled = isProcessing;
            btnSubmitPembetulan.innerHTML = isProcessing ? label : initialSubmitLabel;
        }

        function clearAttachmentClientError() {
            if (!attachmentClientError) {
                return;
            }

            attachmentClientError.textContent = '';
            attachmentClientError.style.display = 'none';
        }

        function showAttachmentClientError(message) {
            if (!attachmentClientError) {
                return;
            }

            attachmentClientError.textContent = message;
            attachmentClientError.style.display = 'block';
        }

        function setFileProcessingMessage(message) {
            if (!fileProcessing) {
                return;
            }

            if (!message) {
                fileProcessing.textContent = '';
                fileProcessing.style.display = 'none';

                return;
            }

            fileProcessing.textContent = message;
            fileProcessing.style.display = 'block';
        }

        function revokeAttachmentPreview() {
            if (attachmentPreviewUrl) {
                URL.revokeObjectURL(attachmentPreviewUrl);
                attachmentPreviewUrl = null;
            }
        }

        function hideAttachmentPreview() {
            revokeAttachmentPreview();

            if (attachmentPreviewImage) {
                attachmentPreviewImage.removeAttribute('src');
                attachmentPreviewImage.style.display = 'none';
            }

            if (attachmentPreviewPdf) {
                attachmentPreviewPdf.removeAttribute('src');
                attachmentPreviewPdf.style.display = 'none';
            }

            if (attachmentPreviewCard) {
                attachmentPreviewCard.style.display = 'none';
            }
        }

        function getFileExtension(fileName) {
            return (fileName.split('.').pop() || '').toLowerCase();
        }

        function isSupportedImageFile(file) {
            var extension = getFileExtension(file.name || '');

            return file.type.startsWith('image/') || ['jpg', 'jpeg', 'png'].includes(extension);
        }

        function isSupportedPdfFile(file) {
            return file.type === 'application/pdf' || getFileExtension(file.name || '') === 'pdf';
        }

        function updateAttachmentPreview(file, badge) {
            if (!file || !attachmentPreviewCard || !attachmentPreviewBadge) {
                return;
            }

            revokeAttachmentPreview();
            attachmentPreviewUrl = URL.createObjectURL(file);
            attachmentPreviewBadge.textContent = badge;
            attachmentPreviewCard.style.display = 'block';

            if (isSupportedPdfFile(file)) {
                attachmentPreviewPdf.src = attachmentPreviewUrl;
                attachmentPreviewPdf.style.display = 'block';
                attachmentPreviewImage.style.display = 'none';

                return;
            }

            attachmentPreviewImage.src = attachmentPreviewUrl;
            attachmentPreviewImage.style.display = 'block';
            attachmentPreviewPdf.style.display = 'none';
        }

        function updateSelectedFileInfo(file, detail) {
            if (!fileSelected || !fileName || !fileMeta) {
                return;
            }

            fileName.textContent = file.name;
            fileMeta.textContent = detail;
            fileSelected.style.display = 'flex';
        }

        function resetAttachmentState(clearInput) {
            if (clearInput === undefined) {
                clearInput = true;
            }

            if (clearInput && fileInput) {
                fileInput.value = '';
            }

            if (fileSelected) {
                fileSelected.style.display = 'none';
            }

            setFileProcessingMessage('');
            clearAttachmentClientError();
            hideAttachmentPreview();
        }

        function syncAttachmentFile(file) {
            if (!fileInput) {
                return;
            }

            var dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
        }

        function createCompressedFileName(originalName) {
            var baseName = originalName.replace(/\.[^.]+$/, '');

            return baseName + '-compressed.jpg';
        }

        function normalizeImageMimeType(file) {
            var extension = getFileExtension(file.name || '');

            if (file.type === 'image/png' || extension === 'png') {
                return 'image/png';
            }

            return 'image/jpeg';
        }

        function loadImageFromObjectUrl(file) {
            return new Promise(function (resolve, reject) {
                var imageUrl = URL.createObjectURL(file);
                var image = new Image();

                image.onload = function () {
                    URL.revokeObjectURL(imageUrl);
                    resolve(image);
                };

                image.onerror = function () {
                    URL.revokeObjectURL(imageUrl);
                    reject(new Error('decode-object-url-failed'));
                };

                image.src = imageUrl;
            });
        }

        function loadImageFromDataUrl(file) {
            return new Promise(function (resolve, reject) {
                var reader = new FileReader();

                reader.onload = function () {
                    var image = new Image();

                    image.onload = function () {
                        resolve(image);
                    };

                    image.onerror = function () {
                        reject(new Error('decode-data-url-failed'));
                    };

                    image.src = reader.result;
                };

                reader.onerror = function () {
                    reject(new Error('read-file-failed'));
                };

                reader.readAsDataURL(file);
            });
        }

        function loadImageFile(file) {
            if (typeof createImageBitmap === 'function') {
                return createImageBitmap(file).catch(function () {
                    return loadImageFromObjectUrl(file).catch(function () {
                        return loadImageFromDataUrl(file);
                    });
                }).catch(function () {
                    throw new Error('Gambar tidak dapat dibaca. Pastikan file menggunakan format JPG atau PNG yang valid.');
                });
            }

            return loadImageFromObjectUrl(file).catch(function () {
                return loadImageFromDataUrl(file);
            }).catch(function () {
                throw new Error('Gambar tidak dapat dibaca. Pastikan file menggunakan format JPG atau PNG yang valid.');
            });
        }

        function canvasToBlob(canvas, type, quality) {
            return new Promise(function (resolve) {
                canvas.toBlob(resolve, type, quality);
            });
        }

        async function compressImageFile(file) {
            if (file.size <= maxAttachmentBytes) {
                return {
                    file: file,
                    compressed: false,
                    originalSize: file.size,
                };
            }

            var image = await loadImageFile(file);
            var width = image.naturalWidth || image.width;
            var height = image.naturalHeight || image.height;
            var quality = 0.9;

            for (var attempt = 0; attempt < 12; attempt++) {
                var canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                var context = canvas.getContext('2d', { alpha: false });
                if (!context) {
                    throw new Error('Browser tidak dapat memproses gambar ini untuk kompresi.');
                }

                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, width, height);
                context.drawImage(image, 0, 0, width, height);

                var blob = await canvasToBlob(canvas, 'image/jpeg', quality);

                if (blob && blob.size <= maxAttachmentBytes) {
                    return {
                        file: new File([blob], createCompressedFileName(file.name), {
                            type: 'image/jpeg',
                            lastModified: Date.now(),
                        }),
                        compressed: true,
                        originalSize: file.size,
                    };
                }

                if (quality > 0.45) {
                    quality -= 0.1;
                    continue;
                }

                var nextWidth = Math.max(Math.round(width * 0.85), 720);
                var nextHeight = Math.max(Math.round(height * 0.85), 720);

                if (nextWidth === width && nextHeight === height) {
                    break;
                }

                width = nextWidth;
                height = nextHeight;
                quality = 0.82;
            }

            throw new Error('Gambar tidak dapat dikompres hingga maksimal 1 MB. Silakan gunakan gambar dengan resolusi lebih kecil.');
        }

        async function processSelectedAttachment(file) {
            if (!file) {
                resetAttachmentState(false);

                return;
            }

            clearAttachmentClientError();
            setFileProcessingMessage('');

            var isImage = isSupportedImageFile(file);
            var isPdf = isSupportedPdfFile(file);

            if (!isImage && !isPdf) {
                resetAttachmentState();
                showAttachmentClientError('Lampiran harus berupa JPG, PNG, atau PDF.');

                return;
            }

            if (isPdf) {
                if (file.size > maxAttachmentBytes) {
                    resetAttachmentState();
                    showAttachmentClientError('Ukuran file PDF maksimal 1 MB.');

                    return;
                }

                var normalizedPdfFile = file.type
                    ? file
                    : new File([file], file.name, {
                        type: 'application/pdf',
                        lastModified: file.lastModified,
                    });

                syncAttachmentFile(normalizedPdfFile);
                updateSelectedFileInfo(normalizedPdfFile, formatFileSize(normalizedPdfFile.size) + ' • Siap diupload');
                updateAttachmentPreview(normalizedPdfFile, 'PDF');

                return;
            }

            attachmentIsProcessing = true;
            setSubmitProcessingState(true, '<i class="bi bi-hourglass-split"></i> Memproses lampiran...');
            setFileProcessingMessage(file.size > maxAttachmentBytes
                ? 'Gambar sedang dikompres agar ukuran upload maksimal 1 MB...'
                : 'Menyiapkan preview gambar...');

            try {
                var normalizedFile = file.type
                    ? file
                    : new File([file], file.name, {
                        type: normalizeImageMimeType(file),
                        lastModified: file.lastModified,
                    });
                var result = await compressImageFile(file);
                var finalFile = result.compressed ? result.file : normalizedFile;

                syncAttachmentFile(finalFile);

                var detail = result.compressed
                    ? formatFileSize(result.originalSize) + ' → ' + formatFileSize(finalFile.size) + ' • Dikompres otomatis'
                    : formatFileSize(finalFile.size) + ' • Siap diupload';

                updateSelectedFileInfo(finalFile, detail);
                updateAttachmentPreview(finalFile, result.compressed ? 'Gambar Terkompres' : 'Gambar');
                setFileProcessingMessage(result.compressed ? 'Kompresi selesai. Preview menampilkan file yang akan dikirim.' : 'Preview menampilkan file yang akan dikirim.');
            } catch (error) {
                resetAttachmentState();
                showAttachmentClientError(error.message || 'Lampiran tidak dapat diproses.');
            } finally {
                attachmentIsProcessing = false;
                setSubmitProcessingState(false);

                if (resubmitAfterAttachmentProcessing) {
                    resubmitAfterAttachmentProcessing = false;

                    if (fileInput && fileInput.files.length > 0) {
                        pembetulanForm.requestSubmit();
                    }
                }
            }
        }

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                processSelectedAttachment(this.files[0] || null);
            });
        }

        if (fileUploadArea && fileInput) {
            ['dragenter', 'dragover'].forEach(function (eventName) {
                fileUploadArea.addEventListener(eventName, function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    fileUploadArea.classList.add('is-dragover');
                });
            });

            ['dragleave', 'dragend', 'drop'].forEach(function (eventName) {
                fileUploadArea.addEventListener(eventName, function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    fileUploadArea.classList.remove('is-dragover');
                });
            });

            fileUploadArea.addEventListener('drop', function (event) {
                var droppedFile = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files[0] : null;

                if (!droppedFile) {
                    return;
                }

                var dataTransfer = new DataTransfer();
                dataTransfer.items.add(droppedFile);
                fileInput.files = dataTransfer.files;
                fileInput.dispatchEvent(new Event('change'));
            });
        }

        if (pembetulanForm) {
            pembetulanForm.addEventListener('submit', function (event) {
                if (attachmentIsProcessing) {
                    event.preventDefault();
                    resubmitAfterAttachmentProcessing = true;

                    return;
                }

                clearAttachmentClientError();
            });
        }
    });
</script>
@endsection
