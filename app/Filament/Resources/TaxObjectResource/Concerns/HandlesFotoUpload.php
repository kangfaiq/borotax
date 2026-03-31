<?php

namespace App\Filament\Resources\TaxObjectResource\Concerns;

use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

trait HandlesFotoUpload
{
    use WithFileUploads;

    public $fotoUploadTemp;

    /**
     * Simpan foto yang telah diupload ke storage permanen.
     * Dipanggil dari Alpine.js setelah client-side upload selesai.
     */
    public function saveFotoObjek(): array
    {
        if (!$this->fotoUploadTemp) {
            return ['success' => false];
        }

        // Foto lama TIDAK dihapus dari storage agar tetap bisa dilihat di histori perubahan.
        // Path foto lama tercatat di ActivityLog old_values saat handleRecordUpdate().

        $path = $this->fotoUploadTemp->store('foto-objek-pajak', 'public');
        $this->data['foto_objek_path'] = $path;

        $size = Storage::disk('public')->size($path);
        $url = Storage::disk('public')->url($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $this->fotoUploadTemp = null;

        return [
            'success' => true,
            'path' => $path,
            'url' => $url,
            'size' => $size,
            'ext' => $ext,
        ];
    }

    /**
     * Hapus foto objek dari storage dan form.
     */
    public function removeFotoObjek(): void
    {
        $path = $this->data['foto_objek_path'] ?? null;
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        $this->data['foto_objek_path'] = null;
    }
}
