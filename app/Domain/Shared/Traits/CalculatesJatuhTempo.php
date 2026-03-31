<?php

namespace App\Domain\Shared\Traits;

use Carbon\Carbon;

/**
 * Trait CalculatesJatuhTempo
 *
 * Logika perhitungan tanggal jatuh tempo (due date) pajak daerah.
 * Konsisten dengan kalkulator sanksi di web (kalkulator-sanksi.blade.php)
 * dan mobile (penalty_calculator_screen.dart).
 *
 * Aturan Jatuh Tempo:
 *  - SA (sebelum 2024): akhir bulan berikutnya
 *  - SA (Jan 2024 - Jun 2025): tgl 10 hari kerja bulan berikutnya
 *  - SA (>= Jul 2025): tgl 10 hari kerja bulan pertama triwulan berikutnya
 *  - Reklame: masa_berlaku_mulai + 1 bulan - 1 hari
 *  - Air Tanah: akhir bulan berikutnya dari periode
 *
 * Aturan Sanksi:
 * - Masa Pajak sebelum Jan 2024: 2% per bulan
 * - Masa Pajak >= Jan 2024: 1% per bulan
 * - Maksimal 24 bulan keterlambatan
 */
trait CalculatesJatuhTempo
{
    /**
     * Hitung jatuh tempo untuk pajak reklame
     * Rumus: masa_berlaku_mulai + 1 bulan - 1 hari
     *
     * @param mixed $masaBerlakuMulai Tanggal mulai masa berlaku reklame
     * @return Carbon
     */
    public static function hitungJatuhTempoReklame(mixed $masaBerlakuMulai): Carbon
    {
        $date = $masaBerlakuMulai instanceof Carbon ? $masaBerlakuMulai : Carbon::parse($masaBerlakuMulai);
        return $date->copy()->addMonth()->subDay();
    }

    /**
     * Hitung jatuh tempo untuk pajak air tanah.
     * Rumus: akhir bulan berikutnya dari periode.
     *
     * @param string $periodeBulan Format: "2026-03" (Y-m) atau "Maret 2026"
     * @return Carbon
     */
    public static function hitungJatuhTempoAirTanah(string $periodeBulan): Carbon
    {
        // Handle format Y-m (e.g., "2026-03")
        if (preg_match('/^\d{4}-\d{2}$/', trim($periodeBulan))) {
            $periode = Carbon::createFromFormat('Y-m', trim($periodeBulan))->startOfMonth();
            return $periode->copy()->addMonth()->endOfMonth();
        }

        // Handle format nama bulan Indonesia (e.g., "Maret 2026")
        $bulanMap = [
            'januari' => 1,
            'februari' => 2,
            'maret' => 3,
            'april' => 4,
            'mei' => 5,
            'juni' => 6,
            'juli' => 7,
            'agustus' => 8,
            'september' => 9,
            'oktober' => 10,
            'november' => 11,
            'desember' => 12,
        ];

        $parts = explode(' ', strtolower(trim($periodeBulan)));
        $bulan = $bulanMap[$parts[0]] ?? 1;
        $tahun = (int) ($parts[1] ?? date('Y'));

        // Akhir bulan berikutnya
        $periode = Carbon::create($tahun, $bulan, 1);
        return $periode->copy()->addMonth()->endOfMonth();
    }

    /**
     * Hitung jatuh tempo untuk pajak self-assessment (Hotel, Restoran, Parkir, Hiburan)
     *
     * @param int $masaPajakBulan Bulan masa pajak (1-12)
     * @param int $masaPajakTahun Tahun masa pajak
     * @return Carbon
     */
    public static function hitungJatuhTempoSelfAssessment(int $masaPajakBulan, int $masaPajakTahun): Carbon
    {
        $periodDate = Carbon::create($masaPajakTahun, $masaPajakBulan, 1);

        if ($periodDate->lt(Carbon::create(2024, 1, 1))) {
            // Sebelum 2024: Akhir bulan berikutnya
            return $periodDate->copy()->addMonths(2)->startOfMonth()->subDay();
        }

        // >= 2024: 10 hari kerja
        if ($periodDate->gte(Carbon::create(2025, 7, 1))) {
            // Triwulan (>= Jul 2025)
            if ($masaPajakBulan <= 3) {
                $dueMonth = 4;
                $dueYear = $masaPajakTahun;
            } elseif ($masaPajakBulan <= 6) {
                $dueMonth = 7;
                $dueYear = $masaPajakTahun;
            } elseif ($masaPajakBulan <= 9) {
                $dueMonth = 10;
                $dueYear = $masaPajakTahun;
            } else {
                $dueMonth = 1;
                $dueYear = $masaPajakTahun + 1;
            }
            $startMonth = Carbon::create($dueYear, $dueMonth, 1);
        } else {
            // Bulanan (Jan 2024 - Jun 2025)
            $startMonth = $periodDate->copy()->addMonth()->startOfMonth();
        }

        return self::getNthWorkingDay($startMonth, 10);
    }

    /**
     * Hitung tarif sanksi berdasarkan masa pajak
     *
     * @param Carbon $masaPajak Tanggal masa pajak
     * @return float 0.02 (2%) untuk sebelum 2024, 0.01 (1%) untuk >= 2024
     */
    public static function getTarifSanksi(Carbon $masaPajak): float
    {
        return $masaPajak->lt(Carbon::create(2024, 1, 1)) ? 0.02 : 0.01;
    }

    /**
     * Hitung jumlah bulan keterlambatan
     *
     * @param Carbon $jatuhTempo Tanggal jatuh tempo
     * @param Carbon $tanggalBayar Tanggal pembayaran / sekarang
     * @return int 0 jika tidak terlambat, 1-24 jika terlambat
     */
    public static function hitungBulanTerlambat(Carbon $jatuhTempo, Carbon $tanggalBayar): int
    {
        if ($tanggalBayar->lte($jatuhTempo)) {
            return 0;
        }

        $yearDiff = $tanggalBayar->year - $jatuhTempo->year;
        $monthDiff = $tanggalBayar->month - $jatuhTempo->month;
        $totalMonths = ($yearDiff * 12) + $monthDiff;

        if ($tanggalBayar->day > $jatuhTempo->day) {
            $totalMonths++;
        }

        if ($totalMonths <= 0) {
            $totalMonths = 1;
        }

        // Maksimal 24 bulan
        return min($totalMonths, 24);
    }

    /**
     * Hitung total sanksi
     *
     * @param float $pokokPajak Nominal pokok pajak
     * @param Carbon $masaPajak Tanggal masa pajak (untuk menentukan tarif)
     * @param Carbon $jatuhTempo Tanggal jatuh tempo
     * @param Carbon|null $tanggalBayar Tanggal bayar (default: sekarang)
     * @param bool $isOpd Jika true, tidak dikenakan denda (OPD exempt)
     * @param bool $isInsidentil Jika true, tidak dikenakan denda (insidentil exempt)
     * @return array ['bulan_terlambat', 'tarif_sanksi', 'denda', 'total']
     */
    public static function hitungSanksi(
        float $pokokPajak,
        Carbon $masaPajak,
        Carbon $jatuhTempo,
        ?Carbon $tanggalBayar = null,
        bool $isOpd = false,
        bool $isInsidentil = false
    ): array {
        $tanggalBayar = $tanggalBayar ?? Carbon::now();
        $bulanTerlambat = self::hitungBulanTerlambat($jatuhTempo, $tanggalBayar);

        // OPD dan Insidentil tidak dikenakan denda meskipun terlambat
        if ($isOpd || $isInsidentil) {
            return [
                'bulan_terlambat' => $bulanTerlambat,
                'tarif_sanksi' => 0, // Not applicable
                'denda' => 0,
                'total' => $pokokPajak,
            ];
        }

        $denda = 0;
        $currentMonth = $jatuhTempo->copy()->startOfMonth();
        
        // Loop for each delayed month, up to maximum $bulanTerlambat (which is capped at 24)
        for ($i = 1; $i <= $bulanTerlambat; $i++) {
            // Next month of delay
            $currentMonth->addMonth();

            // Tarif 2% untuk masa s.d Desember 2023, 1% untuk Januari 2024 dan selebihnya.
            // Note: Peraturan denda keterlambatan biasanya mengikuti "kapan bulan denda tersebut jatuh",
            // atau mengikuti "Masa Pajak" aslinya.
            // Sesuai rules: "dendanya dikunci di desember 2023, ketika masuk di januari 2024 maka menggunakan tarif sanksi 1%"
            // Ini berarti denda tiap bulan berjalan melihat bulan kalender saat denda itu terbentuk.
            $tarifBulanIni = $currentMonth->lt(Carbon::create(2024, 1, 1)) ? 0.02 : 0.01;
            
            $denda += $pokokPajak * $tarifBulanIni;
        }

        $total = $pokokPajak + $denda;
        
        // As tarif can vary, we return the tarif of the initial masa pajak for backward compatibility,
        // although the calculation uses the dynamic split rate.
        $initialTarif = self::getTarifSanksi($masaPajak);

        return [
            'bulan_terlambat' => $bulanTerlambat,
            'tarif_sanksi' => $initialTarif, 
            'denda' => $denda,
            'total' => $total,
        ];
    }

    /**
     * Get N-th working day dari awal bulan (skip weekend & libur nasional)
     *
     * @param Carbon $startMonth Tanggal awal bulan
     * @param int $n Hari kerja ke-n
     * @return Carbon
     */
    private static function getNthWorkingDay(Carbon $startMonth, int $n): Carbon
    {
        $date = $startMonth->copy();
        $count = 0;
        $safety = 0;

        while ($count < $n && $safety < 60) {
            if (self::isWorkingDay($date)) {
                $count++;
            }
            if ($count === $n) {
                return $date;
            }
            $date->addDay();
            $safety++;
        }

        return $date;
    }

    /**
     * Check apakah tanggal adalah hari kerja (bukan weekend / libur)
     */
    private static function isWorkingDay(Carbon $date): bool
    {
        if ($date->isWeekend()) {
            return false;
        }
        return !self::isHoliday($date);
    }

    /**
     * Check apakah tanggal adalah hari libur nasional
     * Daftar libur nasional & cuti bersama 2024-2026
     */
    private static function isHoliday(Carbon $date): bool
    {
        $dateInt = $date->year * 10000 + $date->month * 100 + $date->day;

        $holidays = [
            // 2024
            20240101,
            20240208,
            20240209,
            20240210,
            20240214,
            20240311,
            20240312,
            20240329,
            20240408,
            20240409,
            20240410,
            20240411,
            20240412,
            20240415,
            20240501,
            20240509,
            20240510,
            20240523,
            20240524,
            20240601,
            20240617,
            20240618,
            20240707,
            20240817,
            20240916,
            20241225,
            20241226,
            // 2025 (Estimasi)
            20250101,
            20250127,
            20250129,
            20250329,
            20250331,
            20250401,
            20250418,
            20250501,
            20250512,
            20250529,
            20250601,
            20250607,
            20250627,
            20250817,
            20250905,
            20251225,
            20251226,
            // 2026 (Estimasi)
            20260101,
            20260116,
            20260217,
            20260319,
            20260320,
            20260321,
            20260323,
            20260324,
            20260325,
            20260403,
            20260501,
            20260514,
            20260527,
            20260531,
            20260601,
            20260616,
            20260817,
            20260825,
            20261225,
            20261226,
        ];

        return in_array($dateInt, $holidays);
    }
}
