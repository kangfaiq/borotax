<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Master\Models\JenisPajak;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TarifPajak;
use App\Domain\Tax\Models\TaxObject;
use App\Enums\TaxStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends BaseController
{
    /**
     * Create Self Assessment Billing
     */
    public function createSelfAssessment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tax_object_id' => 'required|exists:tax_objects,id',
            'periode_bulan' => 'required|integer|min:1|max:12', // Month 1-12
            'periode_tahun' => 'required|integer',
            'omzet' => 'required|numeric|min:0',
            'attachment' => 'nullable|image|max:2048', // Dokumen pendukung
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $user = $request->user();
        $taxObject = TaxObject::find($request->tax_object_id);

        // Verifikasi kepemilikan
        if ($taxObject->nik_hash !== $user->nik_hash) {
            return $this->sendError('Objek pajak tidak valid.', [], 404);
        }

        // Generate Billing Code (format Pemda 18 karakter)
        $jenisPajak = JenisPajak::find($taxObject->jenis_pajak_id);
        $billingCode = Tax::generateBillingCode($jenisPajak->kode ?? '41102');

        // Hitung Pajak (Omzet * Tarif Objek)
        // Tarif persen ambil dari tarif_pajak (versioned) atau fallback ke object
        $tanggalMasaPajak = "{$request->periode_tahun}-" . str_pad($request->periode_bulan, 2, '0', STR_PAD_LEFT) . "-01";
        $tarifInfo = TarifPajak::lookupWithDasarHukum($taxObject->sub_jenis_pajak_id, $tanggalMasaPajak);
        $tarif = $tarifInfo['tarif_persen'] ?? $taxObject->tarif_persen ?? 10.0;
        $dasarHukum = $tarifInfo['dasar_hukum'] ?? null;
        $amount = $request->omzet * ($tarif / 100);

        // Handle Attachment
        $attachmentUrl = null;
        if ($request->hasFile('attachment')) {
            $attachmentUrl = $request->file('attachment')->store('tax-attachments', 'public');
        }

        $tax = Tax::create([
            'jenis_pajak_id' => $taxObject->jenis_pajak_id,
            'sub_jenis_pajak_id' => $taxObject->sub_jenis_pajak_id,
            'user_id' => $user->id,
            'amount' => $amount, // Encrypted
            'omzet' => $request->omzet, // Encrypted
            'tarif_persentase' => $tarif,
            'status' => TaxStatus::Pending, // Waiting payment
            'billing_code' => $billingCode,
            'attachment_url' => $attachmentUrl, // Encrypted
            'created_at' => now(),
            'notes' => "Periode: {$request->periode_tahun}-{$request->periode_bulan}",
            'dasar_hukum' => $dasarHukum,
        ]);

        return $this->sendResponse($tax, 'Billing berhasil dibuat.');
    }

    /**
     * Get Transaction History (All Taxes)
     */
    public function getTransactions(Request $request)
    {
        $user = $request->user();

        $transactions = Tax::where('user_id', $user->id)
            ->with(['jenisPajak:id,nama'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse($transactions, 'Riwayat Transaksi Pajak.');
    }

    /**
     * Check Billing
     */
    public function checkBilling(Request $request)
    {
        $code = $request->query('code');

        if (!$code) {
            return $this->sendError('Kode Billing diperlukan.', [], 400);
        }

        $tax = Tax::wherePaymentCode($code)
            ->with(['jenisPajak:id,nama', 'subJenisPajak:id,nama'])
            ->first();

        if (!$tax) {
            return $this->sendError('Billing tidak ditemukan.', [], 404);
        }

        // Return public info of billing (don't expose sensitive user data unless auth user is owner)
        // For E-Payment menu usually minimal data is shown.

        return $this->sendResponse($tax, 'Detail Billing.');
    }
}
