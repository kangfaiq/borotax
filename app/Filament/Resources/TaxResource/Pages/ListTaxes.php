<?php

namespace App\Filament\Resources\TaxResource\Pages;

use Filament\Schemas\Components\Tabs\Tab;
use App\Domain\Master\Models\JenisPajak;
use App\Filament\Pages\LaporanPendapatan;
use App\Filament\Resources\TaxResource;
use App\Enums\TaxStatus;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class ListTaxes extends ListRecords
{
    protected static string $resource = TaxResource::class;

    public ?string $tahun = null;
    public ?string $jenisPajakId = null;
    protected ?string $jenisPajakNama = null;
    protected ?string $tipeAssessment = null;

    protected $queryString = [
        'tahun' => ['except' => ''],
        'jenisPajakId' => ['except' => ''],
    ];

    public function mount(): void
    {
        parent::mount();

        $this->tahun = request()->query('tahun', $this->tahun);
        $this->jenisPajakId = request()->query('jenisPajakId', $this->jenisPajakId);
    }

    protected function getJenisPajakNama(): ?string
    {
        if ($this->jenisPajakNama === null && $this->jenisPajakId) {
            $jp = JenisPajak::find($this->jenisPajakId);
            $this->jenisPajakNama = $jp?->nama;
            $this->tipeAssessment = $jp?->tipe_assessment;
        }
        return $this->jenisPajakNama;
    }

    protected function getTipeAssessment(): ?string
    {
        if ($this->tipeAssessment === null && $this->jenisPajakId) {
            $this->getJenisPajakNama();
        }
        return $this->tipeAssessment;
    }

    public function isOfficialAssessment(): bool
    {
        return $this->getTipeAssessment() === 'official_assessment';
    }

    public function getTabs(): array
    {
        // Official assessment (Reklame, Air Tanah): tabs berdasarkan status pembayaran
        if ($this->getTipeAssessment() === 'official_assessment') {
            return [
                'semua' => Tab::make('Semua'),
                'belum_bayar' => Tab::make('Belum Bayar')
                    ->modifyQueryUsing(fn (Builder $query) => $query->whereNotIn('status', [TaxStatus::Paid])),
                'lunas' => Tab::make('Lunas')
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('status', TaxStatus::Paid)),
            ];
        }

        // Self assessment (Hotel, Restoran, dll): tabs berdasarkan pembetulan
        return [
            'semua' => Tab::make('Semua'),
            'original' => Tab::make('Original')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('pembetulan_ke', 0)),
            'pembetulan' => Tab::make('Pembetulan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('pembetulan_ke', '>', 0)),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        $parts = ['Laporan Pendapatan'];

        if ($this->tahun) {
            $parts[] = $this->tahun;
        }
        if ($nama = $this->getJenisPajakNama()) {
            $parts[] = $nama;
        }

        return implode(' — ', $parts);
    }

    public function getBreadcrumbs(): array
    {
        $crumbs = [
            LaporanPendapatan::getUrl() => 'Laporan Pendapatan',
        ];

        if ($this->tahun) {
            $crumbs[LaporanPendapatan::getUrl(['tahun' => $this->tahun])] = 'Tahun ' . $this->tahun;
        }

        if ($nama = $this->getJenisPajakNama()) {
            $crumbs['#'] = $nama;
        }

        return $crumbs;
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if ($this->jenisPajakId) {
            $query->where('jenis_pajak_id', $this->jenisPajakId);
        }

        if ($this->tahun) {
            $query->whereYear('created_at', (int) $this->tahun);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [
            // No Create Action for Report
        ];
    }
}

