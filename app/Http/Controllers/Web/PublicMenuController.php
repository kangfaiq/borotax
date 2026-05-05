<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Models\Destination;
use App\Domain\CMS\Models\News;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Reklame\Models\KelompokLokasiJalan;
use App\Domain\Reklame\Models\ReklameTariff;
use App\Domain\Reklame\Models\ReklameNilaiStrategis;

class PublicMenuController extends Controller
{
    /**
     * Produk Hukum — Daftar Peraturan Pajak
     */
    public function legalProducts()
    {
        $products = [
            [
                'category' => 'Undang-Undang',
                'title'    => 'Undang-Undang Nomor 1 Tahun 2022 tentang Hubungan Keuangan antara Pemerintah Pusat dan Pemerintah Daerah',
                'year'     => '2022',
                'url'      => 'https://drive.google.com/file/d/14HbPaVXt4x1AZl5HuTawVoWgVZ1pkaKq/view',
            ],
            [
                'category' => 'Peraturan Pemerintah',
                'title'    => 'Peraturan Pemerintah Nomor 35 Tahun 2023 tentang Ketentuan Umum Pajak Daerah dan Retribusi Daerah',
                'year'     => '2023',
                'url'      => 'https://drive.google.com/file/d/1JBRPmPJKWVakwyHHrO8Fonfv2Wf7AwEn/view',
            ],
            [
                'category' => 'Peraturan Menteri Keuangan',
                'title'    => 'Peraturan Menteri Keuangan Nomor 70 Tahun 2022 tentang Kriteria dan/atau Rincian Makanan dan Minuman, Jasa Kesenian dan Hiburan, Jasa Perhotelan, Jasa Penyediaan Tempat Parkir, serta Jasa Boga atau Katering, yang Tidak Dikenai Pajak Pertambahan Nilai',
                'year'     => '2022',
                'url'      => 'https://drive.google.com/file/d/13aahrsK7PWSrM496biUz_xBPWl0oarZE/view',
            ],
            [
                'category' => 'Peraturan Gubernur',
                'title'    => 'Peraturan Gubernur Jawa Timur Nomor 2 Tahun 2022 tentang Harga Dasar Air sebagai Dasar Perhitungan Nilai Perolehan Air Tanah',
                'year'     => '2022',
                'url'      => 'https://drive.google.com/file/d/1S0YbEbsFc10PREXJSwE9UV0li8lcosKR/view',
            ],
            [
                'category' => 'Peraturan Gubernur (Lampiran)',
                'title'    => 'Lampiran Peraturan Gubernur Jawa Timur Nomor 2 Tahun 2022 tentang Harga Dasar Air sebagai Dasar Perhitungan Nilai Perolehan Air Tanah',
                'year'     => '2022',
                'url'      => 'https://drive.google.com/file/d/1fXpE6Kl0QlZLr_KfonIpaFrWO2VYEdHK/view',
            ],
            [
                'category' => 'Keputusan Gubernur',
                'title'    => 'Keputusan Gubernur Jawa Timur Nomor 188/621/KPTS/013/2023 tentang Penetapan Harga Patokan Penjualan Mineral Bukan Logam dan Batuan di Provinsi Jawa Timur',
                'year'     => '2023',
                'url'      => 'https://drive.google.com/file/d/199kJYAjtvRs9zbnkYk4gsdtPY9Vh508M/view',
            ],
            [
                'category' => 'Peraturan Daerah',
                'title'    => 'Peraturan Daerah Kabupaten Bojonegoro Nomor 5 Tahun 2023 tentang Pajak Daerah dan Retribusi Daerah',
                'year'     => '2023',
                'url'      => 'https://drive.google.com/file/d/1f8CjKOqvJ9kilBYkY22oCMYmkeCairws/view',
            ],
            [
                'category' => 'Peraturan Daerah',
                'title'    => 'Peraturan Daerah Kabupaten Bojonegoro Nomor 8 Tahun 2025 tentang Perubahan atas Peraturan Daerah Nomor 5 Tahun 2023 tentang Pajak Daerah dan Retribusi Daerah',
                'year'     => '2025',
                'url'      => 'https://drive.google.com/file/d/1PE02OYyze86mfnkUvebAmiPQ2r4oTkk8/view',
            ],
            [
                'category' => 'Peraturan Bupati',
                'title'    => 'Peraturan Bupati Bojonegoro Nomor 29 Tahun 2024 tentang Tata Cara Pemungutan Pajak Daerah',
                'year'     => '2024',
                'url'      => 'https://drive.google.com/file/d/1nYpeGGhKFMlV3nAC7Rl3e-zF8Wle5EcS/view',
            ],
            [
                'category' => 'Peraturan Bupati',
                'title'    => 'Peraturan Bupati Bojonegoro Nomor 70 Tahun 2025 tentang Perubahan atas Peraturan Bupati Bojonegoro Nomor 29 Tahun 2024 tentang Tata Cara Pemungutan Pajak Daerah',
                'year'     => '2025',
                'url'      => 'https://drive.google.com/file/d/1sCxsdj99K6t-fMx0aWmI85YvdOxUyh98/view',
            ],
        ];

        return view('portal.publik.produk-hukum', compact('products'));
    }

    /**
     * Kalkulator Sanksi — Estimasi Denda Pajak
     */
    public function penaltyCalculator()
    {
        return view('portal.publik.kalkulator-sanksi');
    }

    /**
     * Kalkulator Air Tanah — Estimasi Pajak Air Tanah
     */
    public function waterTaxCalculator()
    {
        return view('portal.publik.kalkulator-air-tanah');
    }

    /**
     * Kalkulator Reklame — Estimasi Pajak Reklame
     * Data diambil dinamis dari database.
     */
    public function reklameTaxCalculator()
    {
        $today = now()->toDateString();

        // ─── 1. TARIF DATA ───────────────────────────────────────
        // Ambil tarif yang berlaku hari ini, join sub_jenis_pajak
        $tariffs = ReklameTariff::where('is_active', true)
            ->where('berlaku_mulai', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('berlaku_sampai')
                  ->orWhere('berlaku_sampai', '>=', $today);
            })
            ->with('hargaPatokanReklame.subJenisPajak:id,kode,nama,is_insidentil,urutan')
            ->orderBy('harga_patokan_reklame_id')
            ->get();

        // Group tarif: per sub_jenis + satuan_waktu → tarif per kelompok / tarif tunggal
        $tarifData = [];
        $grouped = $tariffs->groupBy(fn ($t) => $t->harga_patokan_reklame_id . '|' . $t->satuan_waktu);

        foreach ($grouped as $key => $items) {
            $first = $items->first();
            $hargaPatokan = $first->hargaPatokanReklame;
            if (!$hargaPatokan) continue;

            $entry = [
                'id' => $hargaPatokan->kode . '_' . $first->satuan_waktu,
                'nama' => $hargaPatokan->nama,
                'sub' => null,
                'is_insidentil' => (bool) $hargaPatokan->is_insidentil,
                'satuan' => $first->satuan_waktu,
                'satuanLabel' => $first->satuan_label,
                'urutan' => $hargaPatokan->urutan,
            ];

            // Parse sub-nama dari nama (e.g. "Billboard / Papan Nama / Tinplat (≥10m²)" → sub = "di atas 10m²")
            if (str_contains($hargaPatokan->nama, '(≥10m²)') || str_contains($hargaPatokan->nama, '(>=10m²)')) {
                $entry['nama'] = str_replace([' (≥10m²)', ' (>=10m²)'], '', $hargaPatokan->nama);
                $entry['sub'] = 'di atas 10m²';
            } elseif (str_contains($hargaPatokan->nama, '(<10m²)')) {
                $entry['nama'] = str_replace(' (<10m²)', '', $hargaPatokan->nama);
                $entry['sub'] = 'di bawah 10m²';
            } elseif (str_contains($hargaPatokan->nama, '(Insidentil)') || str_contains($hargaPatokan->nama, '(insidentil)')) {
                $entry['nama'] = str_replace([' (Insidentil)', ' (insidentil)'], '', $hargaPatokan->nama);
                $entry['sub'] = 'Insidentil';
            }

            if ($first->kelompok_lokasi === null) {
                // Insidentil: tarif tunggal
                $entry['tarifTunggal'] = (int) $first->tarif_pokok;
                $entry['tarifPerKelompok'] = null;
            } else {
                // Tetap: tarif per kelompok
                $entry['tarifTunggal'] = null;
                $entry['tarifPerKelompok'] = [];
                foreach ($items as $item) {
                    $entry['tarifPerKelompok'][$item->kelompok_lokasi] = (int) $item->tarif_pokok;
                }
            }

            $tarifData[] = $entry;
        }

        // Sort: tetap first (by urutan), then insidentil (by urutan)
        usort($tarifData, function ($a, $b) {
            $catA = (int) $a['is_insidentil'];
            $catB = (int) $b['is_insidentil'];
            if ($catA !== $catB) return $catA - $catB;
            return ($a['urutan'] ?? 99) - ($b['urutan'] ?? 99);
        });

        // ─── 2. LOKASI DATA ─────────────────────────────────────
        $lokasiRaw = KelompokLokasiJalan::active()
            ->orderBy('kelompok')
            ->orderBy('nama_jalan')
            ->get();

        $kelompokDescriptions = KelompokLokasiJalan::getKelompokOptions();

        $lokasiData = [];
        foreach ($lokasiRaw->groupBy('kelompok') as $kelompok => $items) {
            $desc = $kelompokDescriptions[$kelompok] ?? $kelompok;
            // Extract description after " — "
            $descParts = explode(' — ', $desc);
            $lokasiData[$kelompok] = [
                'label' => 'Kelompok ' . $kelompok,
                'desc' => $descParts[1] ?? $descParts[0],
                'streets' => $items->pluck('nama_jalan')->values()->toArray(),
            ];
        }

        // ─── 3. NILAI STRATEGIS RATES ────────────────────────────
        $nsRaw = ReklameNilaiStrategis::where('is_active', true)
            ->orderBy('kelas_kelompok')
            ->orderBy('luas_min', 'desc')
            ->get();

        $nsRates = [];
        foreach ($nsRaw as $ns) {
            $kelas = $ns->kelas_kelompok;
            $sizeKey = $ns->luas_min >= 25 ? 'big' : 'med';
            $nsRates[$kelas][$sizeKey] = [
                'tahun' => (int) $ns->tarif_per_tahun,
                'bulan' => (int) $ns->tarif_per_bulan,
            ];
        }

        // ─── 4. REKLAME TETAP FOR NS ─────────────────────────────
        // Jenis reklame tetap yang eligible untuk nilai strategis
        $reklameTetapForNs = HargaPatokanReklame::whereHas('subJenisPajak', fn ($q) => $q->where('kode', 'REKLAME_TETAP'))
            ->where('is_active', true)
            ->orderBy('urutan')
            ->pluck('nama')
            ->map(function ($nama) {
                // Normalize: hilangkan suffix (≥10m²) dll agar match
                return preg_replace('/\s*\([^)]*\)$/', '', $nama);
            })
            ->unique()
            ->values()
            ->toArray();

        return view('portal.publik.kalkulator-reklame', compact(
            'tarifData',
            'lokasiData',
            'nsRates',
            'reklameTetapForNs',
        ));
    }

    /**
     * Sewa Reklame — Info Lokasi & Tarif Sewa
     */
    public function sewaReklame()
    {
        AsetReklamePemkab::syncExpiredOpdBorrowings();

        $asetReklame = AsetReklamePemkab::where('is_active', true)
            ->with(['skpdReklame' => function ($q) {
                $q->whereIn('status', ['disetujui', 'draft', 'menungguVerifikasi'])
                  ->where('masa_berlaku_sampai', '>=', now())
                  ->with('permohonanSewa')
                  ->orderByDesc('masa_berlaku_sampai');
            }, 'permohonanSewa' => function ($q) {
                $q->whereIn('status', ['diajukan', 'diproses', 'disetujui'])
                  ->orderByDesc('created_at');
            }])
            ->orderBy('jenis')
            ->orderBy('nama')
            ->get()
            ->map(function ($aset) {
                $data = [
                    'id'                   => $aset->id,
                    'kode_aset'            => $aset->kode_aset,
                    'nama'                 => $aset->nama,
                    'jenis'                => $aset->jenis,
                    'lokasi'               => $aset->lokasi,
                    'kawasan'              => $aset->kawasan,
                    'panjang'              => $aset->panjang,
                    'lebar'                => $aset->lebar,
                    'luas_m2'              => $aset->luas_m2,
                    'jumlah_muka'          => $aset->jumlah_muka,
                    'ukuran_formatted'     => $aset->ukuranFormatted,
                    'harga_sewa_per_tahun' => $aset->harga_sewa_per_tahun,
                    'harga_sewa_per_bulan' => $aset->harga_sewa_per_bulan,
                    'harga_sewa_per_minggu'=> $aset->harga_sewa_per_minggu,
                    'status_ketersediaan'  => $aset->status_ketersediaan,
                    'status_label'         => $aset->statusLabel,
                    'status_color'         => $aset->statusColor,
                    'lat'                  => $aset->latitude,
                    'lng'                  => $aset->longitude,
                    'foto_path'            => $aset->foto_path,
                    'keterangan'           => $aset->keterangan,
                    'penyewa_aktif'        => null,
                ];

                // Info penyewa aktif untuk aset yang sedang disewa/dipinjam
                if ($aset->status_ketersediaan !== 'tersedia') {
                    $skpdAktif = $aset->skpdReklame->first();
                    $permohonanAktif = $aset->permohonanSewa->first();

                    if ($skpdAktif) {
                        $permohonanSkpd = $skpdAktif->permohonanSewa;
                        $data['penyewa_aktif'] = [
                            'materi'        => $permohonanSkpd?->jenis_reklame_dipasang ?? $permohonanAktif?->jenis_reklame_dipasang ?? $skpdAktif->nama_reklame,
                            'jenis_reklame' => $skpdAktif->jenis_reklame,
                            'mulai'         => $skpdAktif->masa_berlaku_mulai?->format('d/m/Y'),
                            'sampai'        => $skpdAktif->masa_berlaku_sampai?->format('d/m/Y'),
                        ];
                    } elseif ($permohonanAktif) {
                        // Fallback: ada permohonan aktif tapi SKPD belum dibuat
                        $durasiHari = $permohonanAktif->durasi_sewa_hari;
                        $satuan = $permohonanAktif->satuan_sewa ?? 'bulan';
                        $jumlah = match ($satuan) {
                            'tahun'  => max(1, (int) round($durasiHari / 365)),
                            'bulan'  => max(1, (int) round($durasiHari / 30)),
                            'minggu' => max(1, (int) round($durasiHari / 7)),
                            default  => $durasiHari,
                        };
                        $mulai = $permohonanAktif->tanggal_mulai_diinginkan;
                        $sampai = $mulai ? match ($satuan) {
                            'tahun'  => $mulai->copy()->addYears($jumlah)->subDay(),
                            'bulan'  => $mulai->copy()->addMonths($jumlah)->subDay(),
                            'minggu' => $mulai->copy()->addWeeks($jumlah)->subDay(),
                            default  => $mulai->copy()->addDays($durasiHari)->subDay(),
                        } : null;

                        $data['penyewa_aktif'] = [
                            'materi'        => $permohonanAktif->jenis_reklame_dipasang,
                            'jenis_reklame' => null,
                            'mulai'         => $mulai?->format('d/m/Y'),
                            'sampai'        => $sampai?->format('d/m/Y'),
                        ];
                    }
                }

                return $data;
            });

        return view('portal.publik.sewa-reklame', [
            'asetReklame' => $asetReklame,
        ]);
    }

    /**
     * Destinasi Wisata — Halaman List
     */
    public function destinations()
    {
        $category = request('category');

        $query = Destination::latest();

        if ($category && in_array($category, ['wisata', 'kuliner', 'hotel', 'oleh-oleh', 'hiburan'])) {
            $query->category($category);
        }

        $destinations = $query->paginate(12)->withQueryString();

        $categories = [
            'wisata' => 'Wisata',
            'kuliner' => 'Kuliner',
            'hotel' => 'Hotel',
            'oleh-oleh' => 'Oleh-Oleh',
            'hiburan' => 'Hiburan',
        ];

        return view('portal.publik.destinasi', compact('destinations', 'categories', 'category'));
    }

    /**
     * Destinasi Wisata — Halaman Detail
     */
    public function destinationDetail(Destination $destination)
    {
        return view('portal.publik.destinasi-detail', compact('destination'));
    }

    /**
     * Berita — Daftar Berita
     */
    public function newsList()
    {
        $category = request('category');

        $query = News::published()->latestPublished();

        if ($category && in_array($category, ['pengumuman', 'pajak', 'event', 'edukasi', 'lainnya'])) {
            $query->category($category);
        }

        $news = $query->paginate(12)->withQueryString();

        $categories = [
            'pengumuman' => 'Pengumuman',
            'pajak' => 'Informasi Pajak',
            'event' => 'Event',
            'edukasi' => 'Edukasi',
            'lainnya' => 'Lainnya',
        ];

        return view('portal.publik.berita', compact('news', 'categories', 'category'));
    }

    /**
     * Berita — Halaman Detail
     */
    public function newsDetail(News $news)
    {
        $news->incrementViewCount();

        $relatedNews = News::published()
            ->where('id', '!=', $news->id)
            ->latestPublished()
            ->take(3)
            ->get();

        return view('portal.publik.berita-detail', compact('news', 'relatedNews'));
    }
}
