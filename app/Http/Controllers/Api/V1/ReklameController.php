<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Reklame\Models\PermohonanSewaReklame;
use App\Domain\Reklame\Models\ReklameObject;
use App\Domain\Reklame\Models\ReklameRequest;
use App\Domain\Shared\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReklameController extends BaseController
{
    /**
     * Get User's Reklame Objects
     */
    public function getObjects(Request $request)
    {
        $user = $request->user();

        $objects = ReklameObject::where('nik_hash', $user->nik_hash)
            ->where('status', 'aktif') // Only active objects
            ->get();

        return $this->sendResponse($objects, 'Daftar Objek Reklame.');
    }

    /**
     * Request Reklame Extension (Perpanjangan)
     */
    public function submitExtension(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tax_object_id' => 'required|exists:tax_objects,id',
            'durasi_perpanjangan_hari' => 'required|integer|in:30,90,180,365',
            'catatan_pengajuan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $user = $request->user();
        $reklame = ReklameObject::find($request->tax_object_id);

        // Verifikasi kepemilikan
        if (! $reklame || $reklame->nik_hash !== $user->nik_hash) {
            return $this->sendError('Objek reklame tidak ditemukan atau bukan milik Anda.', [], 404);
        }

        $hasActiveRequest = ReklameRequest::where('tax_object_id', $reklame->id)
            ->whereIn('status', ['diajukan', 'menungguVerifikasi', 'diproses'])
            ->exists();

        if ($hasActiveRequest) {
            return $this->sendError('Anda sudah memiliki pengajuan perpanjangan yang sedang diproses.', [], 422);
        }

        if (! ($reklame->isKadaluarsa() || $reklame->sisa_hari <= 30)) {
            return $this->sendError('Perpanjangan hanya dapat diajukan ketika objek sudah kedaluwarsa atau sisa masa berlaku maksimal 30 hari.', [], 422);
        }

        $requestReklame = ReklameRequest::create([
            'tax_object_id' => $reklame->id,
            'user_id' => $user->id,
            'user_nik' => $user->nik, // Encrypted
            'user_name' => $user->nama_lengkap, // Encrypted
            'tanggal_pengajuan' => now(),
            'durasi_perpanjangan_hari' => $request->durasi_perpanjangan_hari,
            'catatan_pengajuan' => $request->catatan_pengajuan,
            'status' => 'diajukan',
        ]);

        NotificationService::notifyRole(
            'petugas',
            'Pengajuan Perpanjangan Reklame Baru',
            "Pengajuan perpanjangan reklame baru dari {$user->nama_lengkap} menunggu diproses."
        );

        return $this->sendResponse($requestReklame, 'Pengajuan perpanjangan berhasil dikirim.');
    }

    /**
     * Get Request History
     */
    public function getRequests(Request $request)
    {
        $user = $request->user();

        $requests = ReklameRequest::where('user_id', $user->id)
            ->with(['reklameObject:id,nama_objek_pajak'])
            ->orderBy('tanggal_pengajuan', 'desc')
            ->get();

        return $this->sendResponse($requests, 'Riwayat Pengajuan Reklame.');
    }

    /**
     * Get Available Aset Reklame Pemkab
     */
    public function getAsetPemkab(Request $request)
    {
        $query = AsetReklamePemkab::where('is_active', true);

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status_ketersediaan', $request->status);
        }

        $aset = $query->orderBy('kode_aset')->get()->map(fn ($item) => [
            'id' => $item->id,
            'kode_aset' => $item->kode_aset,
            'nama' => $item->nama,
            'jenis' => $item->jenis,
            'lokasi' => $item->lokasi,
            'panjang' => $item->panjang,
            'lebar' => $item->lebar,
            'luas_m2' => $item->luas_m2,
            'jumlah_muka' => $item->jumlah_muka,
            'harga_sewa_per_tahun' => $item->harga_sewa_per_tahun,
            'harga_sewa_per_bulan' => $item->harga_sewa_per_bulan,
            'status_ketersediaan' => $item->status_ketersediaan,
        ]);

        return $this->sendResponse($aset, 'Daftar Aset Reklame Pemkab.');
    }

    /**
     * Submit Sewa Reklame Permohonan
     */
    public function submitSewa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'aset_reklame_pemkab_id'   => 'required|exists:aset_reklame_pemkab,id',
            'jenis_reklame_dipasang'   => 'required|string|max:255',
            'durasi_sewa_hari'         => 'required|integer|min:1|max:3650',
            'tanggal_mulai_diinginkan' => 'required|date|after_or_equal:today',
            'email'                    => 'nullable|email|max:100',
            'nomor_registrasi_izin'    => 'required|string|max:100',
            'catatan'                  => 'nullable|string|max:1000',
            'file_ktp'                 => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'file_npwp'                => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'file_desain_reklame'      => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $aset = AsetReklamePemkab::where('is_active', true)->find($request->aset_reklame_pemkab_id);

        if (!$aset || $aset->status_ketersediaan !== 'tersedia') {
            return $this->sendError('Aset reklame tidak tersedia untuk disewa.', [], 422);
        }

        $user = $request->user();

        // Cek duplikat permohonan aktif
        $existing = PermohonanSewaReklame::where('aset_reklame_pemkab_id', $request->aset_reklame_pemkab_id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['diajukan', 'perlu_revisi', 'diproses'])
            ->exists();

        if ($existing) {
            return $this->sendError('Anda sudah memiliki permohonan aktif untuk aset ini.', [], 422);
        }

        $fileKtp    = $request->file('file_ktp')->store('sewa-reklame/ktp', 'local');
        $fileNpwp   = $request->hasFile('file_npwp') ? $request->file('file_npwp')->store('sewa-reklame/npwp', 'local') : null;
        $fileDesain = $request->file('file_desain_reklame')->store('sewa-reklame/desain', 'local');

        $permohonan = PermohonanSewaReklame::create([
            'aset_reklame_pemkab_id'   => $request->aset_reklame_pemkab_id,
            'user_id'                  => $user->id,
            'nik'                      => $user->nik,
            'nama'                     => $user->nama_lengkap,
            'alamat'                   => $user->alamat,
            'no_telepon'               => $user->no_telepon,
            'email'                    => $request->email,
            'nama_usaha'               => $user->nama_usaha ?? null,
            'nomor_registrasi_izin'    => $request->nomor_registrasi_izin,
            'jenis_reklame_dipasang'   => $request->jenis_reklame_dipasang,
            'durasi_sewa_hari'         => $request->durasi_sewa_hari,
            'tanggal_mulai_diinginkan' => $request->tanggal_mulai_diinginkan,
            'catatan'                  => $request->catatan,
            'file_ktp'                 => $fileKtp,
            'file_npwp'                => $fileNpwp,
            'file_desain_reklame'      => $fileDesain,
            'status'                   => 'diajukan',
        ]);

        NotificationService::notifyRole(
            'petugas',
            'Permohonan Sewa Reklame Baru',
            "Permohonan sewa reklame baru dari {$user->nama_lengkap} menunggu diproses."
        );

        return $this->sendResponse($permohonan, 'Permohonan sewa reklame berhasil diajukan.');
    }

    /**
     * Get User's Sewa Reklame Permohonan List
     */
    public function getSewaList(Request $request)
    {
        $user = $request->user();

        $permohonan = PermohonanSewaReklame::where('user_id', $user->id)
            ->with('asetReklame:id,kode_aset,nama,lokasi,jenis')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse($permohonan, 'Riwayat Permohonan Sewa Reklame.');
    }
}
