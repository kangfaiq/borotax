<?php

namespace App\Filament\Pages;

use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Retribusi\Models\ObjekRetribusiSewaTanah;
use App\Domain\Retribusi\Models\TarifSewaTanah;
use App\Domain\Retribusi\Services\RetribusiSewaTanahService;
use App\Filament\Resources\SkrdSewaRetribusiResource;
use Carbon\Carbon;
use Exception;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class BuatSkrdSewaTanah extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-check';
    protected static string | \UnitEnum | null $navigationGroup = 'Laporan Petugas';
    protected static ?string $navigationLabel = 'Buat SKRD Sewa Tanah';
    protected static ?string $title = 'Buat SKRD Sewa Tanah';
    protected static ?int $navigationSort = 5;
    protected string $view = 'filament.pages.buat-skrd-sewa-tanah';

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'petugas', 'verifikator']);
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'petugas', 'verifikator']);
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    // Form state
    public ?string $searchObjekKeyword = null;
    public ?string $objekRetribusiId = null;
    public ?string $subJenisPajakId = null;
    public ?int $jumlahReklame = 1;
    public ?int $durasi = 1;
    public ?string $masaBerlakuMulai = null;
    public ?string $masaBerlakuSampai = null;

    // Auto-filled from objek retribusi
    public ?string $nikWajibPajak = null;
    public ?string $namaWajibPajak = null;
    public ?string $alamatWajibPajak = null;
    public ?string $npwpd = null;
    public ?string $namaObjek = null;
    public ?string $alamatObjek = null;
    public ?float $luasM2 = null;

    // Computed preview
    public ?float $previewTarif = null;
    public ?string $previewSatuanLabel = null;
    public ?float $previewJumlahRetribusi = null;
    public float $tarifPajakPersen = 25.00;

    // Options
    public array $subJenisOptions = [];
    public array $objekRetribusiOptions = [];

    public function mount(): void
    {
        $this->masaBerlakuMulai = now()->format('Y-m-d');

        $jenisPajak = JenisPajak::where('kode', '42101')->first();
        if ($jenisPajak) {
            $this->subJenisOptions = SubJenisPajak::where('jenis_pajak_id', $jenisPajak->id)
                ->active()
                ->ordered()
                ->get()
                ->map(fn (SubJenisPajak $sub) => [
                    'id' => $sub->id,
                    'nama' => $sub->nama,
                    'kode' => $sub->kode,
                ])
                ->toArray();
        }

        $this->objekRetribusiOptions = ObjekRetribusiSewaTanah::query()
            ->with('subJenisPajak:id,nama')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (ObjekRetribusiSewaTanah $objek) => [
                'id' => $objek->id,
                'nopd' => (string) $objek->nopd,
                'npwpd' => $objek->npwpd,
                'nama_objek' => $objek->nama_objek,
                'nama_pemilik' => $objek->nama_pemilik,
                'alamat_objek' => $objek->alamat_objek,
                'luas_m2' => (float) $objek->luas_m2,
                'sub_jenis_nama' => $objek->subJenisPajak?->nama,
            ])
            ->toArray();

        $reklame = JenisPajak::where('kode', '41104')->first();
        $this->tarifPajakPersen = $reklame ? (float) $reklame->tarif_default : 25.00;
    }

    public function getFilteredObjekRetribusiOptions(): array
    {
        $keyword = str($this->searchObjekKeyword)->lower()->trim()->toString();

        if ($keyword === '') {
            return $this->objekRetribusiOptions;
        }

        return array_values(array_filter(
            $this->objekRetribusiOptions,
            fn (array $objek) => str($objek['nama_objek'])->lower()->contains($keyword)
                || str($objek['nama_pemilik'])->lower()->contains($keyword)
                || str($objek['npwpd'])->lower()->contains($keyword)
                || str($objek['nopd'])->lower()->contains($keyword)
        ));
    }

    public function selectObjekRetribusi(string $objekId): void
    {
        $this->objekRetribusiId = $objekId;
        $this->updatedObjekRetribusiId();
    }

    public function clearObjekRetribusiSelection(): void
    {
        $this->objekRetribusiId = null;
        $this->resetObjekData();
    }

    public function updatedObjekRetribusiId(): void
    {
        if (! $this->objekRetribusiId) {
            $this->resetObjekData();
            return;
        }

        $objek = ObjekRetribusiSewaTanah::find($this->objekRetribusiId);
        if ($objek) {
            $this->luasM2 = (float) $objek->luas_m2;
            $this->nikWajibPajak = $objek->nik;
            $this->namaWajibPajak = $objek->nama_pemilik;
            $this->alamatWajibPajak = $objek->alamat_pemilik;
            $this->npwpd = $objek->npwpd;
            $this->namaObjek = $objek->nama_objek;
            $this->alamatObjek = $objek->alamat_objek;
            $this->subJenisPajakId = $objek->sub_jenis_pajak_id;
        }

        $this->recalculate();
    }

    public function updatedSubJenisPajakId(): void
    {
        $this->recalculate();
    }

    public function updatedJumlahReklame(): void
    {
        $this->recalculate();
    }

    public function updatedDurasi(): void
    {
        $this->recalculate();
        $this->updateMasaBerlakuSampai();
    }

    public function updatedMasaBerlakuMulai(): void
    {
        $this->updateMasaBerlakuSampai();
    }

    private function resetObjekData(): void
    {
        $this->subJenisPajakId = null;
        $this->luasM2 = null;
        $this->nikWajibPajak = null;
        $this->namaWajibPajak = null;
        $this->alamatWajibPajak = null;
        $this->npwpd = null;
        $this->namaObjek = null;
        $this->alamatObjek = null;
        $this->previewTarif = null;
        $this->previewSatuanLabel = null;
        $this->previewJumlahRetribusi = null;
    }

    private function recalculate(): void
    {
        $this->previewTarif = null;
        $this->previewSatuanLabel = null;
        $this->previewJumlahRetribusi = null;

        if (! $this->subJenisPajakId || ! $this->durasi || $this->durasi < 1 || ! $this->luasM2) {
            return;
        }

        $tarif = TarifSewaTanah::lookupTarif($this->subJenisPajakId);
        if (! $tarif) {
            return;
        }

        $this->previewTarif = (float) $tarif->tarif_nominal;
        $this->previewSatuanLabel = match ($tarif->satuan_waktu) {
            'perTahun' => 'per Tahun',
            'perBulan' => 'per Bulan',
            default => $tarif->satuan_waktu,
        };

        $jumlahReklame = max(1, $this->jumlahReklame ?? 1);
        $this->previewJumlahRetribusi = round(
            $this->luasM2 * $jumlahReklame * $this->previewTarif * ($this->tarifPajakPersen / 100) * $this->durasi
        );
    }

    private function updateMasaBerlakuSampai(): void
    {
        if (! $this->masaBerlakuMulai || ! $this->subJenisPajakId || ! $this->durasi) {
            return;
        }

        $tarif = TarifSewaTanah::lookupTarif($this->subJenisPajakId);
        if (! $tarif) {
            return;
        }

        $mulai = Carbon::parse($this->masaBerlakuMulai);

        $this->masaBerlakuSampai = match ($tarif->satuan_waktu) {
            'perTahun' => $mulai->copy()->addYears($this->durasi)->subDay()->toDateString(),
            'perBulan' => $mulai->copy()->addMonths($this->durasi)->subDay()->toDateString(),
            default => $mulai->copy()->addYears($this->durasi)->subDay()->toDateString(),
        };
    }

    public function simpanDraft(RetribusiSewaTanahService $service): void
    {
        $this->validate([
            'objekRetribusiId' => 'required|exists:objek_retribusi_sewa_tanah,id',
            'subJenisPajakId' => 'required|exists:sub_jenis_pajak,id',
            'jumlahReklame' => 'required|integer|min:1',
            'durasi' => 'required|integer|min:1',
            'masaBerlakuMulai' => 'required|date',
            'masaBerlakuSampai' => 'required|date|after_or_equal:masaBerlakuMulai',
        ]);

        try {
            $skrd = $service->createDraftSkrd([
                'objek_retribusi_id' => $this->objekRetribusiId,
                'sub_jenis_pajak_id' => $this->subJenisPajakId,
                'npwpd' => $this->npwpd,
                'nik_wajib_pajak' => $this->nikWajibPajak,
                'nama_wajib_pajak' => $this->namaWajibPajak,
                'alamat_wajib_pajak' => $this->alamatWajibPajak ?? '-',
                'nama_objek' => $this->namaObjek,
                'alamat_objek' => $this->alamatObjek,
                'jumlah_reklame' => $this->jumlahReklame,
                'durasi' => $this->durasi,
                'masa_berlaku_mulai' => $this->masaBerlakuMulai,
                'masa_berlaku_sampai' => $this->masaBerlakuSampai,
                'petugas_id' => auth()->id(),
                'petugas_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
            ]);

            Notification::make()
                ->title('Draft SKRD Berhasil Dibuat')
                ->body("Nomor: {$skrd->nomor_skrd}")
                ->success()
                ->send();

            $this->redirect(SkrdSewaRetribusiResource::getUrl('index'));
        } catch (Exception $e) {
            Notification::make()
                ->title('Gagal Membuat Draft')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
