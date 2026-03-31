<div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 space-y-3">
    <div class="flex items-start justify-between gap-3">
        <div>
            <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
                <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                Lampiran Pendukung
                <span class="text-[10px] font-bold uppercase tracking-wide text-amber-600 dark:text-amber-400">Opsional</span>
            </label>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                Unggah foto meteran air atau surat pernyataan yang diterima dari wajib pajak melalui WhatsApp pelayanan.
            </p>
        </div>
    </div>

    <div
        wire:ignore
        x-data="abtLampiranUpload()"
        class="space-y-2"
    >
        <input x-ref="fileInput" type="file" class="hidden" accept="image/jpeg,image/png,image/webp,application/pdf" @change="handleFileInput($event)" />

        <div
            x-show="!previewUrl && !isUploading && !isCompressing"
            @click="$refs.fileInput.click()"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop($event)"
            :class="isDragging
                ? 'border-cyan-400 bg-cyan-50 dark:bg-cyan-950/20'
                : 'border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/60'"
            class="flex flex-col items-center justify-center w-full py-8 px-4 border-2 border-dashed rounded-xl cursor-pointer transition-colors"
        >
            <svg class="w-8 h-8 mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <p class="text-sm text-slate-600 dark:text-slate-300 text-center">
                <span class="font-semibold">Klik untuk upload</span> atau seret file ke sini
            </p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 text-center">JPG, PNG, WEBP, PDF. Gambar otomatis dikompres, ukuran akhir maksimal 1 MB.</p>
        </div>

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
                <span class="text-sm text-amber-600 dark:text-amber-400 font-medium">Mengompres lampiran gambar...</span>
            </div>
        </div>

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
                <span class="text-sm text-blue-600 dark:text-blue-400 font-medium">Mengunggah lampiran...</span>
                <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-1.5 mt-1">
                    <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-300" :style="'width:' + uploadProgress + '%'" ></div>
                </div>
            </div>
        </div>

        <div
            x-show="previewUrl && !isUploading && !isCompressing"
            x-cloak
            class="border rounded-xl overflow-hidden border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800"
        >
            <div class="flex items-start gap-3 p-3">
                <div class="flex-shrink-0 cursor-pointer" @click="isImage(fileExt) ? showLightbox = true : window.open(previewUrl, '_blank')" title="Lihat file">
                    <template x-if="isImage(fileExt)">
                        <img :src="previewUrl" alt="Preview lampiran" class="w-16 h-16 object-cover rounded-lg border border-slate-200 dark:border-slate-600 hover:ring-2 hover:ring-cyan-400 transition" />
                    </template>
                    <template x-if="!isImage(fileExt)">
                        <div class="w-16 h-16 flex items-center justify-center bg-red-50 dark:bg-red-900/30 rounded-lg border border-slate-200 dark:border-slate-600 hover:ring-2 hover:ring-cyan-400 transition">
                            <div class="text-center">
                                <svg class="w-6 h-6 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-[10px] font-bold text-red-500 leading-none block mt-0.5">PDF</span>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Lampiran siap diproses</p>
                    <div class="mt-1 space-y-0.5">
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Ukuran file: <span class="font-semibold" x-text="formatSize(fileSize)"></span>
                        </p>
                        <template x-if="originalSize && originalSize > fileSize && isImage(fileExt)">
                            <p class="text-xs text-emerald-600 dark:text-emerald-400">
                                Dikompres dari <span x-text="formatSize(originalSize)"></span>
                            </p>
                        </template>
                    </div>
                </div>

                <button type="button" @click="removeFile()"
                    class="flex-shrink-0 p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                    title="Hapus file">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>

            <div class="border-t border-slate-200 dark:border-slate-700 px-4 py-2">
                <button type="button" @click="$refs.fileInput.click()"
                    class="text-xs text-cyan-600 dark:text-cyan-400 hover:underline font-medium">
                    Ganti lampiran
                </button>
            </div>
        </div>

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
                <button type="button" @click="showLightbox = false"
                    class="absolute -top-3 -right-3 p-1.5 rounded-full bg-white dark:bg-slate-800 shadow-lg text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors"
                    style="z-index: 1000000;"
                    title="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <img :src="previewUrl" alt="Preview lampiran" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain" />
            </div>
        </div>

        <p x-show="errorMessage" x-cloak class="text-xs text-red-500" x-text="errorMessage"></p>
    </div>
</div>