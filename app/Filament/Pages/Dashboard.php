<?php

namespace App\Filament\Pages;

use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\Reklame\Models\ReklameRequest;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Tax\Models\PembetulanRequest;
use App\Domain\Tax\Models\Tax;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Enums\TaxStatus;
use Carbon\Carbon;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    protected static ?int $navigationSort = -2;

    protected string $view = 'filament.pages.dashboard';

    public function getViewData(): array
    {
        $now = Carbon::now();
        $userName = auth()->user()->nama_lengkap ?? auth()->user()->name ?? 'Petugas';

        // --- STATS ---
        $totalWP = WajibPajak::where('status', 'disetujui')->count();
        $wpBulanIni = WajibPajak::where('status', 'disetujui')
            ->whereMonth('tanggal_verifikasi', $now->month)
            ->whereYear('tanggal_verifikasi', $now->year)
            ->count();
        $wpBulanLalu = WajibPajak::where('status', 'disetujui')
            ->whereMonth('tanggal_verifikasi', $now->copy()->subMonth()->month)
            ->whereYear('tanggal_verifikasi', $now->copy()->subMonth()->year)
            ->count();
        $wpTrend = $wpBulanLalu > 0
            ? round((($wpBulanIni - $wpBulanLalu) / $wpBulanLalu) * 100, 1)
            : ($wpBulanIni > 0 ? 100 : 0);

        // Pendapatan bulan ini (hanya yang sudah terbayar)
        $pendapatanBulanIni = Tax::where('status', TaxStatus::Paid)
            ->whereMonth('paid_at', $now->month)
            ->whereYear('paid_at', $now->year)
            ->get()
            ->sum(fn($t) => (float) $t->amount);

        // Pendapatan bulan lalu
        $pendapatanBulanLalu = Tax::where('status', TaxStatus::Paid)
            ->whereMonth('paid_at', $now->copy()->subMonth()->month)
            ->whereYear('paid_at', $now->copy()->subMonth()->year)
            ->get()
            ->sum(fn($t) => (float) $t->amount);

        $pendapatanTrend = $pendapatanBulanLalu > 0
            ? round((($pendapatanBulanIni - $pendapatanBulanLalu) / $pendapatanBulanLalu) * 100, 1)
            : ($pendapatanBulanIni > 0 ? 100 : 0);

        // Billing pending (termasuk SKPD yang sudah verified tapi belum bayar)
        $billingPending = Tax::whereIn('status', [TaxStatus::Pending, TaxStatus::Verified])->count();

        // Total transaksi bulan ini
        $transaksiBulanIni = Tax::where('status', TaxStatus::Paid)
            ->whereMonth('paid_at', $now->month)
            ->whereYear('paid_at', $now->year)
            ->count();

        // --- VERIFIKASI PENDING ---
        $wpMenunggu = WajibPajak::where('status', 'menungguVerifikasi')->count();
        $reklameMenunggu = ReklameRequest::whereIn('status', ['diajukan', 'menungguVerifikasi'])->count();
        $pembetulanMenunggu = PembetulanRequest::where('status', 'pending')->count();
        $skpdReklameMenunggu = SkpdReklame::whereIn('status', ['diterbitkan', 'menungguVerifikasi'])->count();
        $skpdAirMenunggu = SkpdAirTanah::whereIn('status', ['diterbitkan', 'menungguVerifikasi'])->count();

        // --- TRANSAKSI TERBARU ---
        $transaksiTerbaru = Tax::with(['jenisPajak', 'user'])
            ->whereIn('status', [TaxStatus::Paid, TaxStatus::Verified, TaxStatus::Pending])
            ->latest('created_at')
            ->limit(8)
            ->get();

        // --- CHART: Pendapatan 6 bulan ---
        $chartLabels = [];
        $chartValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $chartLabels[] = $m->translatedFormat('M Y');
            $chartValues[] = Tax::where('status', TaxStatus::Paid)
                ->whereMonth('paid_at', $m->month)
                ->whereYear('paid_at', $m->year)
                ->get()
                ->sum(fn($t) => (float) $t->amount);
        }

        // --- CHART: Per jenis pajak ---
        $perJenis = Tax::with('jenisPajak')
            ->where('status', TaxStatus::Paid)
            ->whereMonth('paid_at', $now->month)
            ->whereYear('paid_at', $now->year)
            ->get()
            ->groupBy(fn($t) => $t->jenisPajak->nama ?? 'Lainnya')
            ->map(fn($group) => $group->sum(fn($t) => (float) $t->amount));

        return [
            'userName' => $userName,
            'greeting' => $this->getGreeting(),
            'totalWP' => $totalWP,
            'wpBulanIni' => $wpBulanIni,
            'wpTrend' => $wpTrend,
            'pendapatanBulanIni' => $pendapatanBulanIni,
            'pendapatanTrend' => $pendapatanTrend,
            'billingPending' => $billingPending,
            'transaksiBulanIni' => $transaksiBulanIni,
            'wpMenunggu' => $wpMenunggu,
            'reklameMenunggu' => $reklameMenunggu,
            'pembetulanMenunggu' => $pembetulanMenunggu,
            'skpdReklameMenunggu' => $skpdReklameMenunggu,
            'skpdAirMenunggu' => $skpdAirMenunggu,
            'transaksiTerbaru' => $transaksiTerbaru,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
            'perJenis' => $perJenis,
        ];
    }

    private function getGreeting(): string
    {
        $hour = (int) date('H');
        if ($hour < 12) return 'Selamat Pagi';
        if ($hour < 15) return 'Selamat Siang';
        if ($hour < 18) return 'Selamat Sore';
        return 'Selamat Malam';
    }
}
