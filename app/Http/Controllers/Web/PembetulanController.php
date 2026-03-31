<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Domain\Shared\Services\NotificationService;
use App\Domain\Tax\Models\PembetulanRequest;
use App\Domain\Tax\Models\Tax;
use App\Enums\TaxStatus;
use Illuminate\Http\Request;

class PembetulanController extends Controller
{
    /**
     * Show the pembetulan request form
     */
    public function create(string $taxId)
    {
        $user = auth()->user();

        $tax = Tax::with(['jenisPajak', 'taxObject'])
            ->where('id', $taxId)
            ->where('user_id', $user->id)
            ->whereIn('status', [TaxStatus::Pending, TaxStatus::Paid, TaxStatus::Verified])
            ->firstOrFail();

        // Check if there's already a pending pembetulan request for this tax
        $existingRequest = PembetulanRequest::where('tax_id', $taxId)
            ->where('status', 'pending')
            ->first();

        return view('portal.pembetulan.form', [
            'tax' => $tax,
            'existingRequest' => $existingRequest,
        ]);
    }

    /**
     * Store a new pembetulan request
     */
    public function store(Request $request)
    {
        $request->validate([
            'tax_id' => 'required|uuid|exists:taxes,id',
            'alasan' => 'required|string|min:10|max:1000',
            'omzet_baru' => 'nullable|numeric|min:0',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:1024',
        ], [
            'alasan.required' => 'Alasan pembetulan wajib diisi.',
            'alasan.min' => 'Alasan pembetulan minimal 10 karakter.',
            'omzet_baru.numeric' => 'Omzet baru harus berupa angka.',
            'lampiran.max' => 'Ukuran lampiran maksimal 1MB.',
            'lampiran.mimes' => 'Lampiran harus berupa gambar (JPG, PNG) atau PDF.',
        ]);

        $user = auth()->user();

        // Verify ownership
        $tax = Tax::where('id', $request->tax_id)
            ->where('user_id', $user->id)
            ->whereIn('status', [TaxStatus::Pending, TaxStatus::Paid, TaxStatus::Verified])
            ->firstOrFail();

        // Check if there's already a pending request
        $existingRequest = PembetulanRequest::where('tax_id', $tax->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->withErrors([
                'alasan' => 'Anda sudah memiliki permohonan pembetulan yang sedang menunggu untuk billing ini.',
            ])->withInput();
        }

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('pembetulan/lampiran', 'local');
        }

        PembetulanRequest::create([
            'tax_id' => $tax->id,
            'user_id' => $user->id,
            'alasan' => $request->alasan,
            'omzet_baru' => $request->omzet_baru,
            'lampiran' => $lampiranPath,
            'status' => 'pending',
        ]);

        NotificationService::notifyRole(
            'petugas',
            'Permohonan Pembetulan Billing Baru',
            "Permohonan pembetulan billing baru dari {$user->nama_lengkap} menunggu diproses."
        );

        return redirect()
            ->route('portal.history')
            ->with('success', 'Permohonan pembetulan berhasil diajukan. Petugas akan meninjau permohonan Anda.');
    }
}
