<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Gebyar\Models\GebyarSubmission;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Shared\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GebyarController extends BaseController
{
    /**
     * Submit Gebyar Submission
     */
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_pajak_id' => 'required|exists:jenis_pajak,id',
            'place_name' => 'required|string',
            'transaction_date' => 'required|date',
            'transaction_amount' => 'required|numeric|min:1',
            'image' => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $user = $request->user();

        // Cek Duplikasi (Logic Hash Nominal + Tanggal + Tempat)
        // Di sini simplifikasi dulu
        $amountHash = hash('sha256', (string) $request->transaction_amount);

        // Simpan
        $imagePath = $request->file('image')->store('gebyar-submissions', 'public');

        $submission = GebyarSubmission::create([
            'user_id' => $user->id,
            'user_nik' => $user->nik, // Encrypted
            'user_name' => $user->nama_lengkap, // Encrypted
            'jenis_pajak_id' => $request->jenis_pajak_id,
            'place_name' => $request->place_name, // Encrypted
            'transaction_date' => $request->transaction_date,
            'transaction_amount' => $request->transaction_amount, // Encrypted
            'transaction_amount_hash' => $amountHash,
            'image_url' => $imagePath, // Encrypted
            'status' => 'pending',
            'period_year' => now()->year,
            'kupon_count' => 1, // Default 1 kupon
            'created_at' => now(),
        ]);

        NotificationService::notifyRole(
            'admin',
            'Pengajuan Gebyar Baru',
            "Pengajuan gebyar baru dari {$user->nama_lengkap} menunggu verifikasi."
        );

        return $this->sendResponse($submission, 'Submission berhasil dikirim.');
    }

    /**
     * Get History
     */
    public function getHistory(Request $request)
    {
        $user = $request->user();

        $history = GebyarSubmission::where('user_id', $user->id)
            ->with('jenisPajak:id,nama')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse($history, 'Riwayat Gebyar Sadar Pajak.');
    }
}
