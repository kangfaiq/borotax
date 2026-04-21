<?php

namespace App\Http\Controllers\Web;

use Carbon\Carbon;
use App\Domain\Shared\Services\NotificationService;
use App\Domain\Master\Models\Instansi;
use App\Domain\Tax\Models\PortalMblbSubmission;
use App\Domain\Tax\Models\HargaPatokanSarangWalet;
use App\Http\Controllers\Controller;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TarifPajak;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\Tax\Services\BillingService;
use App\Domain\Tax\Services\MblbService;
use App\Domain\Tax\Services\PpjService;
use App\Domain\Tax\Services\PortalMblbSubmissionService;
use App\Domain\Tax\Services\SarangWaletService;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Enums\TaxStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SelfAssessmentController extends Controller
{
    /**
     * Step 1: Pilih jenis pajak (tax type selection).
     */
    public function index()
    {
        $user = auth()->user();

        // Get self-assessment tax types
        $jenisPajak = JenisPajak::where('tipe_assessment', 'self_assessment')
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        // Count tax objects per jenis pajak for the current user
        $nik = $user->nik;
        $nikHash = TaxObject::generateHash($nik);
        $taxObjectCounts = TaxObject::where('nik_hash', $nikHash)
            ->where('is_active', true)
            ->select('jenis_pajak_id', DB::raw('count(*) as total'))
            ->groupBy('jenis_pajak_id')
            ->pluck('total', 'jenis_pajak_id');

        return view('portal.self-assessment.index', compact('jenisPajak', 'taxObjectCounts'));
    }

    /**
     * Step 2: Form self-assessment untuk jenis pajak tertentu.
     */
    public function create(string $jenisPajakId)
    {
        $user = auth()->user();
        $jenisPajak = JenisPajak::findOrFail($jenisPajakId);

        // Only allow self-assessment types
        if ($jenisPajak->tipe_assessment !== 'self_assessment') {
            abort(403, 'Jenis pajak ini bukan tipe self-assessment.');
        }

        // Get user's tax objects for this type
        $nik = $user->nik;
        $nikHash = TaxObject::generateHash($nik);
        $taxObjects = TaxObject::where('nik_hash', $nikHash)
            ->where('jenis_pajak_id', $jenisPajakId)
            ->where('is_active', true)
            ->with('subJenisPajak')
            ->get();

        // Determine next masa pajak for each tax object
        $nextPeriods = [];
        foreach ($taxObjects as $obj) {
            $nextPeriods[$obj->id] = $this->getNextMasaPajak($obj->id);
        }

        // Get sub jenis pajak
        $subJenisPajak = SubJenisPajak::where('jenis_pajak_id', $jenisPajakId)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        // Sarang Walet: load jenis sarang for the form
        $jenisSarangWalet = collect();
        $hargaSatuanListrikItems = collect();
        $isSarangWalet = $jenisPajak->kode === '41109';
        $isPpj = $jenisPajak->kode === '41105';
        $isMblb = $jenisPajak->kode === '41106';
        $isPpjPln = false;
        $isPpjNonPln = false;
        $mineralItems = collect();
        $opsenPersen = (float) ($jenisPajak->opsen_persen ?? 25);
        if ($isSarangWalet) {
            $jenisSarangWalet = app(SarangWaletService::class)->getAllJenisSarang();
        }
        if ($isPpj) {
            $hargaSatuanListrikItems = app(PpjService::class)->getAllHargaSatuan(now());
            $isPpjPln = $taxObjects->contains(fn (TaxObject $taxObject) => $taxObject->subJenisPajak?->kode === 'PPJ_SUMBER_LAIN');
            $isPpjNonPln = $taxObjects->contains(fn (TaxObject $taxObject) => $taxObject->subJenisPajak?->kode === 'PPJ_DIHASILKAN_SENDIRI');
        }
        if ($isMblb) {
            $mineralItems = app(MblbService::class)->getAllMineralItems();
        }

        $instansiOptions = Instansi::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        return view('portal.self-assessment.create', compact(
            'jenisPajak',
            'taxObjects',
            'subJenisPajak',
            'nextPeriods',
            'jenisSarangWalet',
            'isSarangWalet',
            'isPpj',
            'isPpjPln',
            'isPpjNonPln',
            'isMblb',
            'hargaSatuanListrikItems',
            'mineralItems',
            'opsenPersen',
            'instansiOptions'
        ));
    }

    /**
     * Hitung masa pajak berikutnya untuk objek pajak.
     */
    private function getNextMasaPajak(string $taxObjectId): array
    {
        return app(BillingService::class)->getNextMasaPajak($taxObjectId);
    }

    /**
     * Step 3: Submit self-assessment & generate billing.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $taxObject = TaxObject::with(['jenisPajak', 'subJenisPajak'])->findOrFail($request->tax_object_id);

        // Authorization: check that this tax object belongs to the user
        $nikHash = TaxObject::generateHash($user->nik);
        if ($taxObject->nik_hash !== $nikHash) {
            abort(403, 'Unauthorized access.');
        }

        $isSarangWalet = $taxObject->jenisPajak && $taxObject->jenisPajak->kode === '41109';
        $isPpj = $taxObject->jenisPajak && $taxObject->jenisPajak->kode === '41105';
        $isMblb = $taxObject->jenisPajak && $taxObject->jenisPajak->kode === '41106';

        if ($isSarangWalet) {
            return $this->storeSarangWalet($request, $user, $taxObject);
        }

        if ($isPpj) {
            return $this->storePpj($request, $user, $taxObject);
        }

        if ($isMblb) {
            return $this->storeMblbSubmission($request, $user, $taxObject);
        }

        return $this->storeStandard($request, $user, $taxObject);
    }

    private function storeStandard(Request $request, $user, TaxObject $taxObject)
    {
        $request->validate([
            'tax_object_id' => 'required|exists:tax_objects,id',
            'omzet' => 'required|numeric|min:1',
            'attachment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:1024',
        ], [
            'tax_object_id.required' => 'Pilih objek pajak.',
            'omzet.required' => 'Masukkan jumlah omzet.',
            'omzet.min' => 'Omzet harus lebih dari 0.',
            'attachment.required' => 'Dokumen lampiran wajib diupload.',
            'attachment.max' => 'Ukuran file maksimal 1MB.',
        ]);

        $this->validatePortalBillingNotes($request, $taxObject);

        ['bulan' => $bulan, 'tahun' => $tahun] = $this->resolvePortalMonthlyPeriod($request, $taxObject);

        if (! $taxObject->isMultiBilling()) {
            $duplicateResponse = $this->ensurePortalPeriodAvailable($taxObject, $bulan, $tahun);

            if ($duplicateResponse) {
                return $duplicateResponse;
            }
        }

        $attachmentPath = $request->file('attachment')->store('self-assessment/attachments', 'local');
        $billingSequence = $taxObject->isMultiBilling()
            ? app(BillingService::class)->getNextBillingSequence($taxObject->id, $bulan, $tahun)
            : 0;

        $tax = app(BillingService::class)->generateBillingForPortal([
            'tax_object_id' => $taxObject->id,
            'user_id' => $user->id,
            'omzet' => (float) $request->omzet,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'attachment_url' => $attachmentPath,
            'billing_sequence' => $billingSequence,
            'notes' => $request->input('keterangan'),
        ]);

        return redirect()->route('portal.self-assessment.success', $tax->id);
    }

    private function storePpj(Request $request, $user, TaxObject $taxObject)
    {
        $request->validate([
            'tax_object_id' => 'required|exists:tax_objects,id',
            'attachment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:1024',
        ], [
            'tax_object_id.required' => 'Pilih objek pajak.',
            'attachment.required' => 'Dokumen lampiran wajib diupload.',
            'attachment.max' => 'Ukuran file maksimal 1MB.',
        ]);

        $subJenisKode = $taxObject->subJenisPajak?->kode;

        if ($subJenisKode === 'PPJ_SUMBER_LAIN') {
            $request->validate([
                'pokok_pajak' => 'required|numeric|min:1',
            ], [
                'pokok_pajak.required' => 'Masukkan pokok pajak PPJ.',
                'pokok_pajak.min' => 'Pokok pajak harus lebih dari 0.',
            ]);
        } elseif ($subJenisKode === 'PPJ_DIHASILKAN_SENDIRI') {
            $request->validate([
                'kapasitas_kva' => 'required|numeric|min:0.01',
                'tingkat_penggunaan_persen' => 'required|numeric|min:0.01|max:100',
                'jangka_waktu_jam' => 'required|numeric|min:0.01',
                'harga_satuan_listrik_id' => 'required|exists:harga_satuan_listrik,id',
            ], [
                'kapasitas_kva.required' => 'Masukkan kapasitas tersedia (kVA).',
                'tingkat_penggunaan_persen.required' => 'Masukkan tingkat penggunaan listrik.',
                'tingkat_penggunaan_persen.max' => 'Tingkat penggunaan listrik maksimal 100%.',
                'jangka_waktu_jam.required' => 'Masukkan jangka waktu pemakaian listrik.',
                'harga_satuan_listrik_id.required' => 'Pilih harga satuan listrik.',
            ]);
        } else {
            return back()->withErrors([
                'tax_object_id' => 'Sub jenis pajak PPJ tidak dikenali.',
            ])->withInput();
        }

        $this->validatePortalBillingNotes($request, $taxObject);

        ['bulan' => $bulan, 'tahun' => $tahun] = $this->resolvePortalMonthlyPeriod($request, $taxObject);

        if (! $taxObject->isMultiBilling()) {
            $duplicateResponse = $this->ensurePortalPeriodAvailable($taxObject, $bulan, $tahun);

            if ($duplicateResponse) {
                return $duplicateResponse;
            }
        }

        $tarifInfo = $this->resolvePortalTarifInfo($taxObject, $bulan, $tahun);
        $attachmentPath = $request->file('attachment')->store('self-assessment/attachments', 'local');
        $billingSequence = $taxObject->isMultiBilling()
            ? app(BillingService::class)->getNextBillingSequence($taxObject->id, $bulan, $tahun)
            : 0;
        $ppjService = app(PpjService::class);

        if ($subJenisKode === 'PPJ_SUMBER_LAIN') {
            $tax = $ppjService->generateBillingPpjSumberLain([
                'jenis_pajak_id' => $taxObject->jenis_pajak_id,
                'sub_jenis_pajak_id' => $taxObject->sub_jenis_pajak_id,
                'tax_object_id' => $taxObject->id,
                'user_id' => $user->id,
                'pokok_pajak' => (float) $request->pokok_pajak,
                'tarif_persen' => $tarifInfo['tarif_persen'],
                'bulan' => $bulan,
                'tahun' => $tahun,
                'billing_sequence' => $billingSequence,
                'notes' => $request->input('keterangan'),
                'attachment_url' => $attachmentPath,
                'dasar_hukum' => $tarifInfo['dasar_hukum'],
            ]);

            return redirect()->route('portal.self-assessment.success', $tax->id);
        }

        $hargaSatuan = $ppjService->getAllHargaSatuan(Carbon::create($tahun, $bulan, 1))
            ->firstWhere('id', $request->harga_satuan_listrik_id);

        if (! $hargaSatuan) {
            return back()->withErrors([
                'harga_satuan_listrik_id' => 'Harga satuan listrik tidak tersedia untuk masa pajak ini.',
            ])->withInput();
        }

        $tax = $ppjService->generateBillingPpjNonPln([
            'jenis_pajak_id' => $taxObject->jenis_pajak_id,
            'sub_jenis_pajak_id' => $taxObject->sub_jenis_pajak_id,
            'tax_object_id' => $taxObject->id,
            'user_id' => $user->id,
            'kapasitas_kva' => (float) $request->kapasitas_kva,
            'tingkat_penggunaan_persen' => (float) $request->tingkat_penggunaan_persen,
            'jangka_waktu_jam' => (float) $request->jangka_waktu_jam,
            'harga_satuan' => (float) $hargaSatuan->harga_per_kwh,
            'harga_satuan_listrik_id' => $hargaSatuan->id,
            'tarif_persen' => $tarifInfo['tarif_persen'],
            'bulan' => $bulan,
            'tahun' => $tahun,
            'billing_sequence' => $billingSequence,
            'notes' => $request->input('keterangan'),
            'attachment_url' => $attachmentPath,
            'dasar_hukum' => $tarifInfo['dasar_hukum'],
        ]);

        return redirect()->route('portal.self-assessment.success', $tax->id);
    }

    private function resolvePortalMonthlyPeriod(Request $request, TaxObject $taxObject): array
    {
        $nextPeriod = $this->getNextMasaPajak($taxObject->id);

        if ($nextPeriod['isNew']) {
            $request->validate([
                'bulan' => 'required|integer|min:1|max:12',
                'tahun' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            ], [
                'bulan.required' => 'Pilih bulan masa pajak.',
                'tahun.required' => 'Pilih tahun masa pajak.',
            ]);

            return [
                'bulan' => (int) $request->bulan,
                'tahun' => (int) $request->tahun,
            ];
        }

        return [
            'bulan' => $nextPeriod['bulan'],
            'tahun' => $nextPeriod['tahun'],
        ];
    }

    private function validatePortalBillingNotes(Request $request, TaxObject $taxObject): void
    {
        if (! $taxObject->isMultiBilling()) {
            return;
        }

        $request->validate([
            'keterangan' => 'required|string|min:5|max:500',
        ], [
            'keterangan.required' => 'Keterangan wajib diisi untuk objek pajak ini.',
            'keterangan.min' => 'Keterangan minimal 5 karakter.',
        ]);
    }

    private function ensurePortalPeriodAvailable(TaxObject $taxObject, int $bulan, int $tahun)
    {
        $exists = app(BillingService::class)->billingExistsForPeriod($taxObject->id, $bulan, $tahun);

        if (! $exists) {
            return null;
        }

        $label = Carbon::create()->month($bulan)->translatedFormat('F') . ' ' . $tahun;

        return back()->withErrors([
            'tax_object_id' => 'Billing untuk masa pajak ' . $label . ' sudah dibuat.',
        ])->withInput();
    }

    private function resolvePortalTarifInfo(TaxObject $taxObject, int $bulan, int $tahun): array
    {
        $tanggalMasaPajak = Carbon::create($tahun, $bulan, 1)->toDateString();
        $tarifInfo = TarifPajak::lookupWithDasarHukum($taxObject->sub_jenis_pajak_id, $tanggalMasaPajak);

        return [
            'tarif_persen' => (float) (
                $tarifInfo['tarif_persen']
                ?? $taxObject->tarif_persen
                ?? $taxObject->jenisPajak->tarif_default
                ?? 10
            ),
            'dasar_hukum' => $tarifInfo['dasar_hukum'] ?? null,
        ];
    }

    private function storeMblbSubmission(Request $request, $user, TaxObject $taxObject)
    {
        $request->validate([
            'tax_object_id' => 'required|exists:tax_objects,id',
            'attachment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:8192',
            'volumes' => 'required|array',
            'instansi_id' => 'nullable|exists:instansi,id',
        ], [
            'tax_object_id.required' => 'Pilih objek pajak.',
            'attachment.required' => 'Dokumen lampiran wajib diupload.',
            'attachment.mimes' => 'Lampiran harus berupa JPG, PNG, atau PDF.',
            'volumes.required' => 'Masukkan volume mineral MBLB.',
        ]);

        foreach ($request->input('volumes', []) as $volume) {
            $volumeString = trim((string) $volume);

            if ($volumeString === '') {
                continue;
            }

            if (! preg_match('/^\d+(\.\d{1,2})?$/', $volumeString)) {
                return back()->withErrors([
                    'volumes' => 'Volume mineral maksimal 2 digit desimal.',
                ])->withInput();
            }
        }

        if ($taxObject->isMultiBilling()) {
            $request->validate([
                'keterangan' => 'required|string|min:5|max:500',
            ], [
                'keterangan.required' => 'Keterangan wajib diisi untuk objek pajak ini.',
                'keterangan.min' => 'Keterangan minimal 5 karakter.',
            ]);
        }

        $nextPeriod = $this->getNextMasaPajak($taxObject->id);

        if ($nextPeriod['isNew']) {
            $request->validate([
                'bulan' => 'required|integer|min:1|max:12',
                'tahun' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            ], [
                'bulan.required' => 'Pilih bulan masa pajak.',
                'tahun.required' => 'Pilih tahun masa pajak.',
            ]);

            $bulan = (int) $request->bulan;
            $tahun = (int) $request->tahun;
        } else {
            $bulan = $nextPeriod['bulan'];
            $tahun = $nextPeriod['tahun'];
        }

        if (! $taxObject->isMultiBilling()) {
            $exists = app(BillingService::class)->billingExistsForPeriod($taxObject->id, $bulan, $tahun);

            if ($exists) {
                $label = Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y');
                return back()->withErrors([
                    'tax_object_id' => 'Billing untuk masa pajak ' . $label . ' sudah dibuat.',
                ])->withInput();
            }
        }

        $instansi = $this->resolvePortalInstansi($request, $taxObject);

        $submission = app(PortalMblbSubmissionService::class)->createSubmission(
            $user,
            $taxObject,
            $bulan,
            $tahun,
            $request->input('volumes', []),
            $request->file('attachment'),
            $request->input('keterangan'),
            $instansi,
        );

        NotificationService::notifyRole(
            ['admin', 'verifikator'],
            'Pengajuan Billing MBLB Baru',
            'Pengajuan billing MBLB dari ' . ($user->nama_lengkap ?? $user->name) . ' menunggu verifikasi.'
        );

        return redirect()->route('portal.self-assessment.submission-success', $submission->id);
    }

    private function resolvePortalInstansi(Request $request, TaxObject $taxObject): ?Instansi
    {
        if (($taxObject->subJenisPajak?->kode ?? null) !== 'MBLB_WAPU' || blank($request->instansi_id)) {
            return null;
        }

        $instansi = Instansi::query()
            ->where('is_active', true)
            ->find($request->instansi_id);

        if ($instansi) {
            return $instansi;
        }

        throw ValidationException::withMessages([
            'instansi_id' => 'Instansi yang dipilih tidak valid atau tidak aktif.',
        ]);
    }

    /**
     * Handle Sarang Walet self-assessment submission.
     * Separate flow: jenis sarang + volume (kg) instead of omzet.
     */
    private function storeSarangWalet(Request $request, $user, TaxObject $taxObject)
    {
        $request->validate([
            'tax_object_id' => 'required|exists:tax_objects,id',
            'jenis_sarang_id' => 'required|exists:harga_patokan_sarang_walet,id',
            'volume_kg' => 'required|numeric|min:0.01|max:999999.99',
            'attachment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:1024',
        ], [
            'tax_object_id.required' => 'Pilih objek pajak.',
            'jenis_sarang_id.required' => 'Pilih jenis sarang.',
            'volume_kg.required' => 'Masukkan volume (kg).',
            'volume_kg.min' => 'Volume harus lebih dari 0.',
            'attachment.required' => 'Dokumen lampiran wajib diupload.',
            'attachment.max' => 'Ukuran file maksimal 1MB.',
        ]);

        // Validate volume max 2 decimal places
        $volumeStr = (string) $request->volume_kg;
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $volumeStr)) {
            return back()->withErrors(['volume_kg' => 'Volume maksimal 2 digit desimal.'])->withInput();
        }

        // Determine masa pajak (yearly)
        $nextPeriod = $this->getNextMasaPajak($taxObject->id);

        if ($nextPeriod['isNew']) {
            $request->validate([
                'tahun' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            ], [
                'tahun.required' => 'Pilih tahun masa pajak.',
            ]);
            $tahun = (int) $request->tahun;
        } else {
            $tahun = $nextPeriod['tahun'];
        }

        // Prevent duplicate: cek per tahun
        $exists = Tax::where('tax_object_id', $taxObject->id)
            ->where('masa_pajak_tahun', $tahun)
            ->whereIn('status', [TaxStatus::Pending, TaxStatus::Paid, TaxStatus::Verified])
            ->exists();

        if ($exists) {
            return back()->withErrors(['tax_object_id' => 'Billing untuk tahun ' . $tahun . ' sudah dibuat.'])->withInput();
        }

        // Load jenis sarang
        $jenisSarang = HargaPatokanSarangWalet::findOrFail($request->jenis_sarang_id);
        $hargaPatokan = (float) $jenisSarang->harga_patokan;
        $volumeKg = (float) $request->volume_kg;
        $tarifPersen = (float) ($taxObject->tarif_persen ?? $taxObject->jenisPajak->tarif_default ?? 10);

        // Calculate tax
        $sarangWaletService = app(SarangWaletService::class);
        $calculation = $sarangWaletService->calculateTax($hargaPatokan, $volumeKg, $tarifPersen);

        // Store attachment
        $attachmentPath = $request->file('attachment')->store('self-assessment/attachments', 'local');

        // Lookup dasar hukum
        $tanggalMasaPajak = Carbon::create($tahun, 1, 1)->format('Y-m-d');
        $tarifInfo = TarifPajak::lookupWithDasarHukum($taxObject->sub_jenis_pajak_id, $tanggalMasaPajak);
        $dasarHukum = $tarifInfo['dasar_hukum'] ?? null;

        // Generate billing
        $tax = $sarangWaletService->generateBilling([
            'jenis_pajak_id' => $taxObject->jenis_pajak_id,
            'sub_jenis_pajak_id' => $taxObject->sub_jenis_pajak_id,
            'tax_object_id' => $taxObject->id,
            'user_id' => $user->id,
            'pokok_pajak' => $calculation['pokok_pajak'],
            'dpp' => $calculation['dpp'],
            'tarif_persen' => $tarifPersen,
            'bulan' => null,
            'tahun' => $tahun,
            'harga_patokan_sarang_walet_id' => $jenisSarang->id,
            'jenis_sarang' => $jenisSarang->nama_jenis,
            'volume_kg' => $volumeKg,
            'harga_patokan' => $hargaPatokan,
            'attachment_url' => $attachmentPath,
            'dasar_hukum' => $dasarHukum,
        ]);

        return redirect()->route('portal.self-assessment.success', $tax->id);
    }

    /**
     * Step 4: Success page with billing info.
     */
    public function success(string $taxId)
    {
        $user = auth()->user();
        $tax = Tax::with(['jenisPajak', 'subJenisPajak', 'children:id,parent_tax_id'])->findOrFail($taxId);

        // Check ownership
        if ($tax->user_id !== $user->id) {
            abort(403);
        }

        // Get tax object name
        $nikHash = TaxObject::generateHash($user->nik);
        $taxObject = TaxObject::where('nik_hash', $nikHash)
            ->where('jenis_pajak_id', $tax->jenis_pajak_id)
            ->first();

        return view('portal.self-assessment.success', compact('tax', 'taxObject'));
    }

    public function submissionSuccess(string $submissionId)
    {
        $user = auth()->user();
        $submission = PortalMblbSubmission::with(['taxObject', 'jenisPajak'])->findOrFail($submissionId);

        if ($submission->user_id !== $user->id) {
            abort(403);
        }

        return view('portal.self-assessment.submission-success', compact('submission'));
    }
}
