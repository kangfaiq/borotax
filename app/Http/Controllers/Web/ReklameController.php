<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Reklame\Models\PermohonanSewaReklame;
use App\Domain\Reklame\Models\ReklameObject;
use App\Domain\Reklame\Models\ReklameRequest;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Shared\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReklameController extends Controller
{
    /* ======================================================
     * Hub / Index — ringkasan layanan reklame
     * ====================================================== */
    public function index()
    {
        $user = auth()->user();
        $nikHash = ReklameObject::generateHash($user->nik);

        $totalObjek = ReklameObject::where('nik_hash', $nikHash)->count();

        $objekAktif = ReklameObject::where('nik_hash', $nikHash)
            ->aktif()
            ->count();

        $objekKadaluarsa = ReklameObject::where('nik_hash', $nikHash)
            ->where(function ($q) {
                $q->where('status', 'kadaluarsa')
                  ->orWhere('masa_berlaku_sampai', '<', now());
            })
            ->count();

        $skpdCount = SkpdReklame::whereHas('reklameObject', function ($q) use ($nikHash) {
            $q->where('nik_hash', $nikHash);
        })->count();

        $pengajuanAktif = ReklameRequest::whereHas('reklameObject', function ($q) use ($nikHash) {
            $q->where('nik_hash', $nikHash);
        })->whereIn('status', ['diajukan', 'menungguVerifikasi', 'diproses'])->count();

        return view('portal.reklame.index', compact(
            'totalObjek', 'objekAktif', 'objekKadaluarsa', 'skpdCount', 'pengajuanAktif'
        ));
    }

    /* ======================================================
     * Daftar objek reklame milik WP
     * ====================================================== */
    public function objects()
    {
        $user = auth()->user();
        $nikHash = ReklameObject::generateHash($user->nik);

        $objects = ReklameObject::where('nik_hash', $nikHash)
            ->with(['reklameRequests' => fn ($q) => $q->latest('tanggal_pengajuan')->limit(1)])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('portal.reklame.objects', compact('objects'));
    }

    /* ======================================================
     * Detail objek reklame
     * ====================================================== */
    public function objectDetail(string $objectId)
    {
        $user = auth()->user();
        $nikHash = ReklameObject::generateHash($user->nik);

        $object = ReklameObject::where('nik_hash', $nikHash)
            ->with([
                'reklameRequests' => fn ($q) => $q->orderBy('tanggal_pengajuan', 'desc'),
                'skpdReklame' => fn ($q) => $q->orderBy('created_at', 'desc')->limit(3),
            ])
            ->findOrFail($objectId);

        $fotoHistories = $this->buildMediaHistories(
            ActivityLog::forTarget('tax_objects', $object->id)
                ->with('actor')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get(),
            'foto_objek_path',
            'Foto Objek',
        );

        $materiHistories = $this->buildMediaHistories(
            $this->resolvePermohonanMaterialLogsForObject($object),
            'file_desain_reklame',
            'Materi Reklame',
        );

        // Cek apakah bisa mengajukan perpanjangan:
        // Kadaluarsa ATAU sisa hari <= 30 DAN tidak ada pengajuan aktif
        $hasActiveRequest = $object->reklameRequests
            ->whereIn('status', ['diajukan', 'menungguVerifikasi', 'diproses'])
            ->isNotEmpty();

        $canRequestExtension = !$hasActiveRequest &&
            ($object->isKadaluarsa() || $object->sisa_hari <= 30);

        return view('portal.reklame.object-detail', compact('object', 'canRequestExtension', 'hasActiveRequest', 'fotoHistories', 'materiHistories'));
    }

    /* ======================================================
     * Form pengajuan perpanjangan reklame
     * ====================================================== */
    public function requestExtension(string $objectId)
    {
        $user = auth()->user();
        $nikHash = ReklameObject::generateHash($user->nik);

        $object = ReklameObject::where('nik_hash', $nikHash)->findOrFail($objectId);

        if (! ($object->isKadaluarsa() || $object->sisa_hari <= 30)) {
            return redirect()->route('portal.reklame.object-detail', $objectId)
                ->with('error', 'Perpanjangan hanya dapat diajukan ketika objek sudah kedaluwarsa atau sisa masa berlaku maksimal 30 hari.');
        }

        // Validate eligibility
        $hasActiveRequest = ReklameRequest::where('tax_object_id', $objectId)
            ->whereIn('status', ['diajukan', 'menungguVerifikasi', 'diproses'])
            ->exists();

        if ($hasActiveRequest) {
            return redirect()->route('portal.reklame.object-detail', $objectId)
                ->with('error', 'Anda sudah memiliki pengajuan perpanjangan yang sedang diproses.');
        }

        return view('portal.reklame.request-extension', compact('object'));
    }

    /* ======================================================
     * Simpan pengajuan perpanjangan
     * ====================================================== */
    public function storeExtension(Request $request, string $objectId)
    {
        $user = auth()->user();
        $nikHash = ReklameObject::generateHash($user->nik);

        $object = ReklameObject::where('nik_hash', $nikHash)->findOrFail($objectId);

        // Validate
        $request->validate([
            'durasi_perpanjangan_hari' => 'required|integer|in:30,90,180,365',
            'catatan_pengajuan' => 'nullable|string|max:500',
        ]);

        if (! ($object->isKadaluarsa() || $object->sisa_hari <= 30)) {
            return redirect()->route('portal.reklame.object-detail', $objectId)
                ->with('error', 'Perpanjangan hanya dapat diajukan ketika objek sudah kedaluwarsa atau sisa masa berlaku maksimal 30 hari.');
        }

        // Check no active request
        $hasActiveRequest = ReklameRequest::where('tax_object_id', $objectId)
            ->whereIn('status', ['diajukan', 'menungguVerifikasi', 'diproses'])
            ->exists();

        if ($hasActiveRequest) {
            return redirect()->route('portal.reklame.object-detail', $objectId)
                ->with('error', 'Anda sudah memiliki pengajuan perpanjangan yang sedang diproses.');
        }

        ReklameRequest::create([
            'tax_object_id' => $objectId,
            'user_id' => $user->id,
            'user_nik' => $user->nik,
            'user_name' => $user->nama_lengkap,
            'tanggal_pengajuan' => now(),
            'durasi_perpanjangan_hari' => $request->durasi_perpanjangan_hari,
            'catatan_pengajuan' => $request->catatan_pengajuan,
            'status' => 'diajukan',
        ]);

        return redirect()->route('portal.reklame.object-detail', $objectId)
            ->with('success', 'Pengajuan perpanjangan reklame berhasil dikirim.');
    }

    /* ======================================================
     * Daftar SKPD Reklame
     * ====================================================== */
    public function skpdList(Request $request)
    {
        $user = auth()->user();
        $nikHash = ReklameObject::generateHash($user->nik);

        $tab = $request->get('tab', 'selesai');

        $query = SkpdReklame::whereHas('reklameObject', function ($q) use ($nikHash) {
            $q->where('nik_hash', $nikHash);
        })->with(['reklameObject']);

        if ($tab === 'proses') {
            $query->whereIn('status', ['draft', 'menungguVerifikasi']);
        } else {
            $query->whereIn('status', ['disetujui', 'ditolak']);
        }

        $skpds = $query->orderBy('created_at', 'desc')->get();

        return view('portal.reklame.skpd-list', compact('skpds', 'tab'));
    }

    /* ======================================================
     * Detail SKPD Reklame
     * ====================================================== */
    public function skpdDetail(string $skpdId)
    {
        $user = auth()->user();
        $nikHash = ReklameObject::generateHash($user->nik);

        $skpd = SkpdReklame::whereHas('reklameObject', function ($q) use ($nikHash) {
            $q->where('nik_hash', $nikHash);
        })->with(['reklameObject', 'reklameRequest', 'permohonanSewa'])
          ->findOrFail($skpdId);

        $fotoHistories = $this->buildMediaHistories(
            ActivityLog::forTarget('tax_objects', $skpd->tax_object_id)
                ->with('actor')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get(),
            'foto_objek_path',
            'Foto Objek',
        );

        $materiHistories = $this->buildMediaHistories(
            $this->resolvePermohonanMaterialLogsForSkpd($skpd),
            'file_desain_reklame',
            'Materi Reklame',
        );

        return view('portal.reklame.skpd-detail', compact('skpd', 'fotoHistories', 'materiHistories'));
    }

    // ═══════════════════════════════════════════════════════════════════════
    // SEWA REKLAME ASET PEMKAB (Public — tanpa login)
    // ═══════════════════════════════════════════════════════════════════════

    public function sewaCekTiket(Request $request)
    {
        $nomorTiket = $request->query('nomor_tiket');
        $permohonan = null;

        if ($nomorTiket) {
            $permohonan = PermohonanSewaReklame::where('nomor_tiket', $nomorTiket)
                ->with(['asetReklame', 'skpdReklame'])
                ->first();
        }

        return view('portal.reklame.sewa-cek-tiket', compact('nomorTiket', 'permohonan'));
    }

    public function sewaForm(string $asetId)
    {
        AsetReklamePemkab::syncExpiredOpdBorrowings();

        $aset = AsetReklamePemkab::where('is_active', true)->findOrFail($asetId);

        return view('portal.reklame.sewa-form', compact('aset'));
    }

    public function sewaStore(Request $request, string $asetId)
    {
        AsetReklamePemkab::syncExpiredOpdBorrowings();

        $aset = AsetReklamePemkab::where('is_active', true)->findOrFail($asetId);

        if ($aset->status_ketersediaan !== 'tersedia') {
            return redirect()->route('publik.sewa-reklame')
                ->with('error', 'Aset reklame ini sedang tidak tersedia untuk disewa.');
        }

        $validated = $request->validate([
            'nik'                      => 'required|string|max:20',
            'nama'                     => 'required|string|max:150',
            'alamat'                   => 'required|string|max:500',
            'no_telepon'               => 'required|string|max:20',
            'nama_usaha'               => 'nullable|string|max:255',
            'jenis_reklame_dipasang'   => 'required|string|max:255',
            'jumlah_sewa'              => 'required|integer|min:1',
            'satuan_sewa'              => 'required|in:minggu,bulan,tahun',
            'tanggal_mulai_diinginkan' => 'required|date|after_or_equal:today',
            'email'                    => 'nullable|email|max:100',
            'nomor_registrasi_izin'    => 'required|string|max:100',
            'catatan'                  => 'nullable|string|max:1000',
            'file_ktp'                 => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'file_npwp'                => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'file_desain_reklame'      => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'npwpd'                    => 'nullable|string|max:13',
        ], [
            'jumlah_sewa.max' => 'Maksimal :max :values. Untuk durasi lebih panjang, gunakan satuan yang lebih besar.',
        ]);

        // Validasi batas maksimal per satuan
        $maxPerSatuan = match ($validated['satuan_sewa']) {
            'minggu' => 3,
            'bulan'  => 11,
            default  => 100,
        };
        if ($validated['jumlah_sewa'] > $maxPerSatuan) {
            $saranSatuan = match ($validated['satuan_sewa']) {
                'minggu' => 'bulan',
                'bulan'  => 'tahun',
                default  => null,
            };
            $pesan = "Maksimal {$maxPerSatuan} " . $validated['satuan_sewa'] . ".";
            if ($saranSatuan) {
                $pesan .= " Untuk durasi lebih panjang, silakan gunakan satuan {$saranSatuan}.";
            }
            return back()->withInput()->withErrors(['jumlah_sewa' => $pesan]);
        }

        // Hitung durasi dalam hari dari satuan + jumlah
        $validated['durasi_sewa_hari'] = match ($validated['satuan_sewa']) {
            'minggu' => $validated['jumlah_sewa'] * 7,
            'bulan'  => $validated['jumlah_sewa'] * 30,
            'tahun'  => $validated['jumlah_sewa'] * 365,
        };

        // Cek duplikat permohonan aktif berdasarkan NIK + aset
        $existing = PermohonanSewaReklame::where('aset_reklame_pemkab_id', $asetId)
            ->where('nik', $validated['nik'])
            ->whereIn('status', ['diajukan', 'perlu_revisi', 'diproses'])
            ->exists();

        if ($existing) {
            return back()->withInput()
                ->with('error', 'Sudah ada permohonan aktif untuk aset ini dengan NIK yang sama.');
        }

        $fileKtp     = $request->file('file_ktp')->store('sewa-reklame/ktp', 'local');
        $fileNpwp    = $request->hasFile('file_npwp') ? $request->file('file_npwp')->store('sewa-reklame/npwp', 'local') : null;
        $fileDesain  = $request->file('file_desain_reklame')->store('sewa-reklame/desain', 'local');

        $permohonan = PermohonanSewaReklame::create([
            'aset_reklame_pemkab_id'   => $asetId,
            'nik'                      => $validated['nik'],
            'nama'                     => $validated['nama'],
            'alamat'                   => $validated['alamat'],
            'no_telepon'               => $validated['no_telepon'],
            'email'                    => $validated['email'] ?? null,
            'nama_usaha'               => $validated['nama_usaha'] ?? null,
            'nomor_registrasi_izin'    => $validated['nomor_registrasi_izin'],
            'jenis_reklame_dipasang'   => $validated['jenis_reklame_dipasang'],
            'durasi_sewa_hari'         => $validated['durasi_sewa_hari'],
            'satuan_sewa'              => $validated['satuan_sewa'],
            'tanggal_mulai_diinginkan' => $validated['tanggal_mulai_diinginkan'],
            'catatan'                  => $validated['catatan'] ?? null,
            'file_ktp'                 => $fileKtp,
            'file_npwp'                => $fileNpwp,
            'file_desain_reklame'      => $fileDesain,
            'npwpd'                    => $validated['npwpd'] ?? null,
            'status'                   => 'diajukan',
        ]);

        return redirect()->route('sewa-reklame.detail', $permohonan->nomor_tiket)
            ->with('success', 'Permohonan sewa reklame berhasil diajukan. Simpan nomor tiket Anda: ' . $permohonan->nomor_tiket);
    }

    public function sewaDetail(string $nomorTiket)
    {
        $permohonan = PermohonanSewaReklame::where('nomor_tiket', $nomorTiket)
            ->with(['asetReklame', 'skpdReklame'])
            ->firstOrFail();

        return view('portal.reklame.sewa-detail', compact('permohonan'));
    }

    public function sewaEdit(string $nomorTiket)
    {
        $permohonan = PermohonanSewaReklame::where('nomor_tiket', $nomorTiket)
            ->where('status', 'perlu_revisi')
            ->with('asetReklame')
            ->firstOrFail();

        return view('portal.reklame.sewa-edit', compact('permohonan'));
    }

    public function sewaUpdate(Request $request, string $nomorTiket)
    {
        $permohonan = PermohonanSewaReklame::where('nomor_tiket', $nomorTiket)
            ->where('status', 'perlu_revisi')
            ->firstOrFail();

        $oldDesainPath = $permohonan->file_desain_reklame;

        $validated = $request->validate([
            'jenis_reklame_dipasang'   => 'required|string|max:255',
            'jumlah_sewa'              => 'required|integer|min:1',
            'satuan_sewa'              => 'required|in:minggu,bulan,tahun',
            'tanggal_mulai_diinginkan' => 'required|date|after_or_equal:today',
            'email'                    => 'nullable|email|max:100',
            'nomor_registrasi_izin'    => 'required|string|max:100',
            'catatan'                  => 'nullable|string|max:1000',
            'file_ktp'                 => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'file_npwp'                => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'file_desain_reklame'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'npwpd'                    => 'nullable|string|max:13',
        ]);

        // Validasi batas maksimal per satuan
        $maxPerSatuan = match ($validated['satuan_sewa']) {
            'minggu' => 3,
            'bulan'  => 11,
            default  => 100,
        };
        if ($validated['jumlah_sewa'] > $maxPerSatuan) {
            $saranSatuan = match ($validated['satuan_sewa']) {
                'minggu' => 'bulan',
                'bulan'  => 'tahun',
                default  => null,
            };
            $pesan = "Maksimal {$maxPerSatuan} " . $validated['satuan_sewa'] . ".";
            if ($saranSatuan) {
                $pesan .= " Untuk durasi lebih panjang, silakan gunakan satuan {$saranSatuan}.";
            }
            return back()->withInput()->withErrors(['jumlah_sewa' => $pesan]);
        }

        // Hitung durasi dalam hari dari satuan + jumlah
        $validated['durasi_sewa_hari'] = match ($validated['satuan_sewa']) {
            'minggu' => $validated['jumlah_sewa'] * 7,
            'bulan'  => $validated['jumlah_sewa'] * 30,
            'tahun'  => $validated['jumlah_sewa'] * 365,
        };

        $data = [
            'jenis_reklame_dipasang'   => $validated['jenis_reklame_dipasang'],
            'durasi_sewa_hari'         => $validated['durasi_sewa_hari'],
            'satuan_sewa'              => $validated['satuan_sewa'],
            'tanggal_mulai_diinginkan' => $validated['tanggal_mulai_diinginkan'],
            'email'                    => $validated['email'] ?? null,
            'nomor_registrasi_izin'    => $validated['nomor_registrasi_izin'],
            'catatan'                  => $validated['catatan'] ?? null,
            'npwpd'                    => $validated['npwpd'] ?? null,
            'status'                   => 'diajukan',
            'catatan_petugas'          => null,
        ];

        if ($request->hasFile('file_ktp')) {
            $data['file_ktp'] = $request->file('file_ktp')->store('sewa-reklame/ktp', 'local');
        }
        if ($request->hasFile('file_npwp')) {
            $data['file_npwp'] = $request->file('file_npwp')->store('sewa-reklame/npwp', 'local');
        }
        if ($request->hasFile('file_desain_reklame')) {
            $data['file_desain_reklame'] = $request->file('file_desain_reklame')->store('sewa-reklame/desain', 'local');
        }

        $permohonan->update($data);

        if (array_key_exists('file_desain_reklame', $data) && $oldDesainPath !== ($data['file_desain_reklame'] ?? null)) {
            ActivityLog::log(
                action: ActivityLog::ACTION_UPDATE_REKLAME_MATERIAL_FILE,
                targetTable: $permohonan->getTable(),
                targetId: $permohonan->id,
                description: 'Revisi file materi reklame untuk permohonan sewa dengan nomor tiket ' . $permohonan->nomor_tiket . '.',
                oldValues: ['file_desain_reklame' => $oldDesainPath],
                newValues: ['file_desain_reklame' => $data['file_desain_reklame']],
            );
        }

        return redirect()->route('sewa-reklame.detail', $permohonan->nomor_tiket)
            ->with('success', 'Permohonan berhasil direvisi dan diajukan kembali.');
    }

    private function resolvePermohonanMaterialLogsForObject(ReklameObject $object): Collection
    {
        $permohonanIds = PermohonanSewaReklame::query()
            ->whereHas('skpdReklame', fn ($query) => $query->where('tax_object_id', $object->id))
            ->pluck('id');

        if ($permohonanIds->isEmpty()) {
            return collect();
        }

        return ActivityLog::query()
            ->where('target_table', 'permohonan_sewa_reklame')
            ->whereIn('target_id', $permohonanIds)
            ->with('actor')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }

    private function resolvePermohonanMaterialLogsForSkpd(SkpdReklame $skpd): Collection
    {
        if (! $skpd->permohonan_sewa_id) {
            return collect();
        }

        return ActivityLog::query()
            ->where('target_table', 'permohonan_sewa_reklame')
            ->where('target_id', $skpd->permohonan_sewa_id)
            ->with('actor')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }

    private function buildMediaHistories(Collection $logs, string $field, string $label): Collection
    {
        return $logs
            ->filter(fn (ActivityLog $log): bool => filled(data_get($log->old_values, $field)) || filled(data_get($log->new_values, $field)))
            ->map(fn (ActivityLog $log): array => [
                'id' => $log->id,
                'label' => $label,
                'action_label' => $log->action_label,
                'description' => $log->description,
                'actor_name' => $log->actor?->name ?? 'System',
                'created_at' => $log->created_at,
                'old' => $this->buildMediaHistoryVersion($log, 'old', $field),
                'new' => $this->buildMediaHistoryVersion($log, 'new', $field),
            ])
            ->values();
    }

    private function buildMediaHistoryVersion(ActivityLog $log, string $version, string $field): ?array
    {
        $values = $version === 'old' ? $log->old_values : $log->new_values;
        $path = data_get($values, $field);

        if (! filled($path)) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return [
            'path' => $path,
            'filename' => basename($path),
            'url' => route('activity-logs.file-preview', [
                'activityLog' => $log,
                'version' => $version,
                'field' => $field,
            ]),
            'is_image' => in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true),
            'is_pdf' => $extension === 'pdf',
        ];
    }
}
