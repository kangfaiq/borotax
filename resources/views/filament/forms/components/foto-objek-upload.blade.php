@php
    $livewire = $getLivewire();
    $existingPath = $livewire->data['foto_objek_path'] ?? $livewire->record?->foto_objek_path ?? null;
    $existingUrl = null;
    $existingSize = null;
    $existingExt = null;
    $isReadonly = ! method_exists($livewire, 'saveFotoObjek');

    if ($existingPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($existingPath)) {
        $existingUrl = '/storage/' . ltrim($existingPath, '/');
        $existingSize = \Illuminate\Support\Facades\Storage::disk('public')->size($existingPath);
        $existingExt = strtolower(pathinfo($existingPath, PATHINFO_EXTENSION));
    }
@endphp

@if ($isReadonly)
<div class="space-y-2">
    @if ($existingUrl)
        <div class="border rounded-xl overflow-hidden border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <div class="flex items-start gap-3 p-3">
                <div class="flex-shrink-0">
                    @if (in_array($existingExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true))
                        <a href="{{ $existingUrl }}" target="_blank" rel="noopener noreferrer" title="Lihat foto objek pajak">
                            <img src="{{ $existingUrl }}" alt="Foto objek pajak" class="w-16 h-16 object-cover rounded-lg border border-gray-200 dark:border-gray-600" />
                        </a>
                    @else
                        <a href="{{ $existingUrl }}" target="_blank" rel="noopener noreferrer" class="w-16 h-16 flex items-center justify-center bg-red-50 dark:bg-red-900/30 rounded-lg border border-gray-200 dark:border-gray-600" title="Buka file objek pajak">
                            <div class="text-center">
                                <svg class="w-6 h-6 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-[10px] font-bold text-red-500 leading-none block mt-0.5">{{ strtoupper($existingExt) }}</span>
                            </div>
                        </a>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">File tersimpan</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Ukuran file: <span class="font-semibold">{{ $existingSize ? ($existingSize < 1024 ? $existingSize . ' B' : number_format($existingSize / 1024, 1) . ' KB') : '-' }}</span>
                    </p>
                </div>

                <a href="{{ $existingUrl }}" target="_blank" rel="noopener noreferrer"
                   class="flex-shrink-0 p-1.5 rounded-lg text-blue-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors"
                   title="Buka file">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7m0 0v7m0-7L10 14m-3-7H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2"/>
                    </svg>
                </a>
            </div>
        </div>
    @else
        <div class="border rounded-xl border-dashed border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
            Belum ada foto objek pajak tersimpan.
        </div>
    @endif
</div>
@else
<div
    wire:ignore
    x-data="{
        previewUrl: @js($existingUrl),
        fileSize: @js($existingSize),
        fileExt: @js($existingExt),
        originalSize: null,
        isCompressing: false,
        isUploading: false,
        uploadProgress: 0,
        isDragging: false,
        errorMessage: null,
        showLightbox: false,

        formatSize(bytes) {
            if (!bytes) return '-';
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(2) + ' MB';
        },

        isImage(ext) {
            return ['jpg','jpeg','png','webp','gif'].includes((ext || '').toLowerCase());
        },

        async compressImage(file, maxBytes) {
            return new Promise((resolve, reject) => {
                const url = URL.createObjectURL(file);
                const img = new Image();
                img.onerror = () => { URL.revokeObjectURL(url); reject('Gagal memuat gambar'); };
                img.onload = () => {
                    URL.revokeObjectURL(url);
                    let w = img.width, h = img.height;
                    const maxDim = 2048;
                    if (w > maxDim || h > maxDim) {
                        const r = Math.min(maxDim / w, maxDim / h);
                        w = Math.round(w * r);
                        h = Math.round(h * r);
                    }
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    const attempt = (quality, scale) => {
                        const sw = Math.round(w * scale), sh = Math.round(h * scale);
                        canvas.width = sw;
                        canvas.height = sh;
                        ctx.clearRect(0, 0, sw, sh);
                        ctx.drawImage(img, 0, 0, sw, sh);
                        canvas.toBlob(blob => {
                            if (!blob) { reject('Gagal mengompres'); return; }
                            if (blob.size <= maxBytes || (quality <= 0.15 && scale <= 0.3)) {
                                resolve(new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), { type: 'image/jpeg', lastModified: Date.now() }));
                            } else if (quality > 0.2) {
                                attempt(quality - 0.1, scale);
                            } else {
                                attempt(0.7, Math.max(scale - 0.2, 0.3));
                            }
                        }, 'image/jpeg', quality);
                    };
                    attempt(0.85, 1);
                };
                img.src = url;
            });
        },

        async processFile(file) {
            this.errorMessage = null;
            this.originalSize = file.size;
            const ext = file.name.split('.').pop().toLowerCase();
            let uploadFile = file;

            if (this.isImage(ext)) {
                if (file.size > 1048576) {
                    this.isCompressing = true;
                    try {
                        uploadFile = await this.compressImage(file, 1048576);
                    } catch (err) {
                        this.errorMessage = 'Gagal mengompres gambar: ' + err;
                        this.isCompressing = false;
                        return;
                    }
                    this.isCompressing = false;
                }
            } else if (ext === 'pdf') {
                if (file.size > 1048576) {
                    this.errorMessage = 'Ukuran file PDF maksimal 1 MB. File Anda ' + this.formatSize(file.size) + '.';
                    return;
                }
            } else {
                this.errorMessage = 'Format file tidak didukung. Gunakan JPG, PNG, WEBP, atau PDF.';
                return;
            }

            // Generate client-side preview URL (works for both images and PDF)
            const localPreviewUrl = URL.createObjectURL(uploadFile);

            this.isUploading = true;
            this.uploadProgress = 0;

            $wire.upload('fotoUploadTemp', uploadFile,
                async () => {
                    try {
                        const result = await $wire.saveFotoObjek();
                        if (result.success) {
                            this.previewUrl = localPreviewUrl || result.url;
                            this.fileSize = result.size;
                            this.fileExt = result.ext;
                        } else {
                            this.errorMessage = 'Gagal menyimpan file.';
                        }
                    } catch (e) {
                        this.errorMessage = 'Gagal menyimpan file.';
                    }
                    this.isUploading = false;
                },
                () => {
                    this.errorMessage = 'Gagal mengunggah file.';
                    this.isUploading = false;
                },
                (event) => {
                    this.uploadProgress = event.detail?.progress || 0;
                }
            );
        },

        handleFileInput(event) {
            const file = event.target.files?.[0];
            if (file) this.processFile(file);
        },

        handleDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer?.files?.[0];
            if (file) this.processFile(file);
        },

        async removeFile() {
            await $wire.removeFotoObjek();
            this.previewUrl = null;
            this.fileSize = null;
            this.fileExt = null;
            this.originalSize = null;
            this.errorMessage = null;
            if (this.$refs.fileInput) this.$refs.fileInput.value = '';
        }
    }"
    class="space-y-2"
>
    {{-- Hidden file input --}}
    <input x-ref="fileInput" type="file" class="hidden" accept="image/jpeg,image/png,image/webp,application/pdf" @change="handleFileInput($event)" />

    {{-- Drop / Click area --}}
    <div
        x-show="!previewUrl && !isUploading && !isCompressing"
        @click="$refs.fileInput.click()"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop($event)"
        :class="isDragging
            ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
            : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700/60'"
        class="flex flex-col items-center justify-center w-full py-8 border-2 border-dashed rounded-xl cursor-pointer transition-colors"
    >
        <svg class="w-8 h-8 mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
        </svg>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            <span class="font-semibold">Klik untuk upload</span> atau seret file ke sini
        </p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">JPG, PNG, WEBP, PDF — Gambar dikompres otomatis maks 1 MB</p>
    </div>

    {{-- Compressing state --}}
    <div
        x-show="isCompressing"
        x-cloak
        class="flex items-center justify-center w-full py-8 border-2 border-dashed rounded-xl border-amber-300 dark:border-amber-600 bg-amber-50 dark:bg-amber-900/20"
    >
        <div class="flex flex-col items-center gap-2">
            <svg class="animate-spin h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            <span class="text-sm text-amber-600 dark:text-amber-400 font-medium">Mengompres gambar...</span>
        </div>
    </div>

    {{-- Uploading state --}}
    <div
        x-show="isUploading && !isCompressing"
        x-cloak
        class="flex flex-col items-center justify-center w-full py-8 border-2 border-dashed rounded-xl border-blue-300 dark:border-blue-600 bg-blue-50 dark:bg-blue-900/20"
    >
        <div class="flex flex-col items-center gap-2 w-full px-12">
            <svg class="animate-spin h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            <span class="text-sm text-blue-600 dark:text-blue-400 font-medium">Mengunggah...</span>
            <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-1.5 mt-1">
                <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-300" :style="'width:' + uploadProgress + '%'"></div>
            </div>
        </div>
    </div>

    {{-- Preview --}}
    <div
        x-show="previewUrl && !isUploading && !isCompressing"
        x-cloak
        class="border rounded-xl overflow-hidden border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
    >
        <div class="flex items-start gap-3 p-3">
            {{-- Thumbnail (fixed 16x16 / 64px, clickable) --}}
            <div class="flex-shrink-0 cursor-pointer" @click="isImage(fileExt) ? showLightbox = true : window.open(previewUrl, '_blank')" title="Lihat file">
                <template x-if="isImage(fileExt)">
                    <img :src="previewUrl" alt="Preview" class="w-16 h-16 object-cover rounded-lg border border-gray-200 dark:border-gray-600 hover:ring-2 hover:ring-primary-400 transition" />
                </template>
                <template x-if="!isImage(fileExt)">
                    <div class="w-16 h-16 flex items-center justify-center bg-red-50 dark:bg-red-900/30 rounded-lg border border-gray-200 dark:border-gray-600 hover:ring-2 hover:ring-primary-400 transition">
                        <div class="text-center">
                            <svg class="w-6 h-6 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-[10px] font-bold text-red-500 leading-none block mt-0.5">PDF</span>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">File berhasil diunggah</p>
                <div class="mt-1 space-y-0.5">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Ukuran file: <span class="font-semibold" x-text="formatSize(fileSize)"></span>
                    </p>
                    <template x-if="originalSize && originalSize > fileSize && isImage(fileExt)">
                        <p class="text-xs text-green-600 dark:text-green-400">
                            ✓ Dikompres dari <span x-text="formatSize(originalSize)"></span>
                        </p>
                    </template>
                </div>
            </div>

            {{-- Remove --}}
            <button type="button" @click="removeFile()"
                class="flex-shrink-0 p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                title="Hapus file">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </div>

        {{-- Replace --}}
        <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-2">
            <button type="button" @click="$refs.fileInput.click()"
                class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium">
                Ganti file
            </button>
        </div>
    </div>

    {{-- Lightbox overlay (full-size image view) --}}
    <div
        x-show="showLightbox"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.self="showLightbox = false"
        @keydown.escape.window="showLightbox = false"
        style="z-index: 999999; position: fixed; inset: 0;"
        class="flex items-center justify-center bg-black/80 backdrop-blur-sm p-6"
    >
        <div class="relative max-w-3xl max-h-[85vh] w-full flex items-center justify-center">
            {{-- Close button --}}
            <button type="button" @click="showLightbox = false"
                class="absolute -top-3 -right-3 p-1.5 rounded-full bg-white dark:bg-gray-800 shadow-lg text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
                style="z-index: 1000000;"
                title="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <img :src="previewUrl" alt="Preview" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain" />
        </div>
    </div>

    {{-- Error --}}
    <p x-show="errorMessage" x-cloak class="text-xs text-danger-500" x-text="errorMessage"></p>
</div>
@endif
