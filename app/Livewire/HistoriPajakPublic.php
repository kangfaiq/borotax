<?php

namespace App\Livewire;

use App\Domain\HistoriPajak\Dto\DokumenPajakRow;
use App\Domain\HistoriPajak\Exceptions\WajibPajakTidakDitemukanException;
use App\Domain\HistoriPajak\Services\HistoriPajakService;
use App\Enums\HistoriPajakAccessStatus;
use App\Models\HistoriPajakAccessLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HistoriPajakPublic extends Component
{
    public string $npwpd = '';

    public ?int $tahun = null;

    public string $turnstileToken = '';

    /** @var array<int, array<string, mixed>> */
    public array $rows = [];

    /** @var array<string, mixed> */
    public array $ringkasan = [
        'total_dokumen' => 0,
        'total_tagihan' => 0.0,
        'total_terbayar' => 0.0,
        'total_tunggakan' => 0.0,
    ];

    public bool $sudahCari = false;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->tahun = (int) now()->year;
    }

    #[On('turnstile-success')]
    public function setTurnstileToken(string $token): void
    {
        $this->turnstileToken = $token;
    }

    /**
     * @return array<int, int>
     */
    public function getDaftarTahunProperty(): array
    {
        $tahunSekarang = (int) now()->year;
        $list = [];
        for ($t = $tahunSekarang; $t >= 2019; $t--) {
            $list[] = $t;
        }

        return $list;
    }

    public function cari(HistoriPajakService $service, RateLimiter $limiter): void
    {
        $this->errorMessage = null;
        $this->rows = [];
        $this->sudahCari = false;
        $this->ringkasan = [
            'total_dokumen' => 0,
            'total_tagihan' => 0.0,
            'total_terbayar' => 0.0,
            'total_tunggakan' => 0.0,
        ];

        $ip = (string) request()->ip();
        $userAgent = (string) Str::limit((string) request()->userAgent(), 510, '');
        $rateKey = 'histori-pajak:' . $ip;

        if ($limiter->tooManyAttempts($rateKey, 5)) {
            $this->logAccess($ip, $userAgent, HistoriPajakAccessStatus::RATE_LIMITED, 0);
            $this->errorMessage = 'Terlalu banyak percobaan. Silakan coba lagi dalam beberapa menit.';

            return;
        }

        $limiter->hit($rateKey, 60 * 15);

        $this->validate([
            'npwpd' => ['required', 'string', 'regex:/^\d{13}$/'],
            'tahun' => ['required', 'integer', 'min:2019', 'max:' . now()->year],
        ], [
            'npwpd.required' => 'NPWPD wajib diisi.',
            'npwpd.regex' => 'Format NPWPD harus 13 digit angka.',
            'tahun.required' => 'Tahun pajak wajib dipilih.',
        ]);

        if (! $this->verifyCaptcha($this->turnstileToken)) {
            $this->logAccess($ip, $userAgent, HistoriPajakAccessStatus::GAGAL_CAPTCHA, 0);
            $this->errorMessage = 'Verifikasi captcha gagal. Silakan coba lagi.';
            $this->turnstileToken = '';
            $this->dispatch('turnstile-reset');

            return;
        }

        try {
            $rows = $service->cari($this->npwpd, (int) $this->tahun);
        } catch (WajibPajakTidakDitemukanException $e) {
            $this->logAccess($ip, $userAgent, HistoriPajakAccessStatus::GAGAL_NPWPD_TIDAK_DITEMUKAN, 0);
            $this->errorMessage = 'NPWPD tidak ditemukan. Pastikan nomor yang Anda masukkan benar.';
            $this->turnstileToken = '';
            $this->dispatch('turnstile-reset');

            return;
        }

        $this->rows = $this->mapRowsToArray($rows);
        $this->ringkasan = $service->ringkasan($rows);
        $this->sudahCari = true;
        $this->turnstileToken = '';
        $this->dispatch('turnstile-reset');

        $this->logAccess($ip, $userAgent, HistoriPajakAccessStatus::SUKSES, $rows->count());
    }

    public function eksporExcel(HistoriPajakService $service): ?StreamedResponse
    {
        if (! $this->sudahCari || empty($this->rows)) {
            $this->errorMessage = 'Tidak ada data untuk diekspor.';

            return null;
        }

        if (! class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            $this->errorMessage = 'Modul ekspor Excel belum tersedia.';

            return null;
        }

        $rows = $service->cari($this->npwpd, (int) $this->tahun);
        $ringkasan = $service->ringkasan($rows);

        $filename = 'Histori-Pajak-' . $this->npwpd . '-' . $this->tahun . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\HistoriPajakExport($rows, $ringkasan, $this->npwpd, (int) $this->tahun),
            $filename
        );
    }

    public function cetakPdf(HistoriPajakService $service)
    {
        if (! $this->sudahCari || empty($this->rows)) {
            $this->errorMessage = 'Tidak ada data untuk dicetak.';

            return null;
        }

        $rows = $service->cari($this->npwpd, (int) $this->tahun);
        $ringkasan = $service->ringkasan($rows);

        $filename = 'Histori-Pajak-' . $this->npwpd . '-' . $this->tahun . '.pdf';

        // Folio / F4 landscape: 215 x 330 mm = 609.45 x 935.43 pt
        $pdf = Pdf::loadView('pdf.histori-pajak', [
            'rows' => $rows,
            'ringkasan' => $ringkasan,
            'npwpd' => $this->npwpd,
            'tahun' => (int) $this->tahun,
            'tanggalCetak' => now(),
        ])->setPaper([0, 0, 935.43, 609.45], 'portrait'); // landscape Folio

        return response()->streamDownload(
            fn () => print($pdf->stream()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function render()
    {
        return view('livewire.histori-pajak-public', [
            'daftarTahun' => $this->daftarTahun,
        ]);
    }

    /**
     * @param  Collection<int, DokumenPajakRow>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function mapRowsToArray(Collection $rows): array
    {
        return $rows->map(fn (DokumenPajakRow $row) => [
            'jenis_dokumen' => $row->jenisDokumen->value,
            'jenis_dokumen_label' => $row->jenisDokumen->label(),
            'jenis_dokumen_color' => $row->jenisDokumen->badgeColor(),
            'jenis_pajak' => $row->jenisPajak,
            'nopd' => $row->nopd,
            'nama_objek_pajak' => $row->namaObjekPajak,
            'nomor' => $row->nomor,
            'masa' => $row->masa,
            'tanggal_terbit' => $row->tanggalTerbit?->translatedFormat('d M Y'),
            'jatuh_tempo' => $row->jatuhTempo?->translatedFormat('d M Y'),
            'jumlah_tagihan' => $row->jumlahTagihan,
            'jumlah_terbayar' => $row->jumlahTerbayar,
            'jumlah_sisa' => $row->jumlahSisa(),
            'status_label' => $row->statusLabel,
            'status' => $row->status,
        ])->all();
    }

    private function verifyCaptcha(string $token): bool
    {
        $secret = config('services.turnstile.secret');

        if (empty($secret)) {
            // Tidak ada konfigurasi — anggap captcha lulus (mode pengembangan).
            return true;
        }

        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => request()->ip(),
                ]
            );

            return (bool) $response->json('success', false);
        } catch (ConnectionException $e) {
            Log::warning('Turnstile verifikasi gagal (jaringan).', ['msg' => $e->getMessage()]);

            return false;
        }
    }

    private function logAccess(string $ip, string $userAgent, HistoriPajakAccessStatus $status, int $jumlah): void
    {
        HistoriPajakAccessLog::create([
            'npwpd' => $this->npwpd ?: null,
            'tahun' => (int) ($this->tahun ?? now()->year),
            'ip' => $ip,
            'user_agent' => $userAgent,
            'status' => $status,
            'jumlah_dokumen' => $jumlah,
        ]);
    }
}
