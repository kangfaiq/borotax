<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\AirTanah\Models\WaterObject;
use App\Domain\AirTanah\Models\MeterReport;
use App\Domain\Shared\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WaterTaxController extends BaseController
{
    /**
     * Get User's Water Objects
     */
    public function getObjects(Request $request)
    {
        $user = $request->user();

        // Ambil objek air tanah milik user
        $objects = WaterObject::where('nik_hash', $user->nik_hash) // Cari berdasarkan NIK Hash pemilik
            ->where('is_active', true)
            ->get();

        // Catatan: Objek air tanah sekarang di tabel tax_objects
        // dengan filter jenis_pajak via WaterObject global scope.

        return $this->sendResponse($objects, 'Daftar Objek Air Tanah.');
    }

    /**
     * Register New Water Object
     */
    public function registerObject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_objek' => 'required|string',
            'alamat_objek' => 'required|string',
            'kecamatan' => 'required|string',
            'kelurahan' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'foto_objek' => 'nullable|image|max:2048', // Max 2MB
            'jenis_sumber' => 'required|in:sumurBor,sumurGali,matAir,springWell',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $user = $request->user();

        // Cari Jenis Pajak Air Tanah (Kode 41108)
        $jenisPajak = JenisPajak::where('kode', '41108')->first();
        if (!$jenisPajak) {
            return $this->sendError('Konfigurasi Jenis Pajak Air Tanah belum tersedia.', [], 500);
        }

        // Cari Default Sub Jenis (Misal Air Tanah Dalam)
        // Idealnya user pilih sub jenis, tapi untuk simplifikasi kita ambil yang pertama atau default
        $subJenis = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)->first();

        // Handle Upload Foto
        $fotoPath = null;
        if ($request->hasFile('foto_objek')) {
            $fotoPath = $request->file('foto_objek')->store('water-objects', 'public');
        }

        // Generate NOPD Dummy (Nanti diganti logic generate NOPD real)
        $nopd = rand(100000, 999999);

        // Create Object (di tabel tax_objects via WaterObject model)
        $object = WaterObject::create([
            'nik' => $user->nik, // Encrypted by Model
            'nik_hash' => $user->nik_hash,
            'nama_objek_pajak' => $request->nama_objek, // Encrypted
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenis?->id ?? $jenisPajak->id, // Fallback
            'jenis_sumber' => $request->jenis_sumber,
            'npwpd' => $user->wajibPajak?->npwpd ?? 'NPWPD-' . $user->nik, // Fallback dummy
            'nopd' => $nopd,
            'alamat_objek' => $request->alamat_objek, // Encrypted
            'kelurahan' => $request->kelurahan,
            'kecamatan' => $request->kecamatan,
            'latitude' => (string) $request->latitude, // Encrypted
            'longitude' => (string) $request->longitude, // Encrypted
            'tanggal_daftar' => now(),
            'is_active' => true,
            'foto_objek_path' => $fotoPath, // Encrypted
        ]);

        return $this->sendResponse($object, 'Objek Pajak Air Tanah berhasil didaftarkan.');
    }

    /**
     * Submit Meter Report
     */
    public function submitReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tax_object_id' => 'required|exists:tax_objects,id',
            'meter_reading_before' => 'required|integer',
            'meter_reading_after' => 'required|integer|gte:meter_reading_before',
            'foto_meter' => 'required|image|max:2048',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $user = $request->user();

        $object = WaterObject::active()->find($request->tax_object_id);

        if (! $object || $object->nik_hash !== $user->nik_hash) {
            return $this->sendError('Objek air tanah tidak ditemukan atau bukan milik Anda.', [], 404);
        }

        // Handle Upload
        $fotoPath = $request->file('foto_meter')->store('meter-reports', 'public');

        // Logic Location Verification (Simplifikasi: Anggap verified jika coordinate dikirim)
        $isLocationVerified = true;

        // Note: Di production, cek jarak antara lat/long request dengan lat/long object

        $report = MeterReport::create([
            'tax_object_id' => $request->tax_object_id,
            'user_id' => $user->id,
            'user_nik' => $user->nik, // Encrypted
            'user_name' => $user->nama_lengkap, // Encrypted
            'meter_reading_before' => $request->meter_reading_before,
            'meter_reading_after' => $request->meter_reading_after,
            // 'usage' calculated automatically in Model boot
            'photo_url' => $fotoPath, // Encrypted
            'latitude' => (string) $request->latitude, // Encrypted
            'longitude' => (string) $request->longitude, // Encrypted
            'location_verified' => $isLocationVerified,
            'status' => 'submitted',
            'reported_at' => now(),
        ]);

        // Update last reading di object
        $object->update([
            'last_meter_reading' => $request->meter_reading_after,
            'last_report_date' => now(),
        ]);

        NotificationService::notifyRole(
            'petugas',
            'Laporan Meter Air Tanah Baru',
            "Laporan meter air tanah baru dari {$user->nama_lengkap} menunggu diproses."
        );

        return $this->sendResponse($report, 'Laporan Meter berhasil dikirim.');
    }

    /**
     * Get Report History
     */
    public function getHistory(Request $request)
    {
        $user = $request->user();

        $history = MeterReport::where('user_id', $user->id)
            ->with(['waterObject:id,nama_objek_pajak']) // Eager load minimal fields
            ->orderBy('reported_at', 'desc')
            ->get();

        return $this->sendResponse($history, 'Riwayat Laporan Meter.');
    }
}
