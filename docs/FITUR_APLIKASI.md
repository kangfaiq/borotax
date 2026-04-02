# Dokumentasi Fitur Aplikasi BOROTAX

> **Sistem Pajak Daerah Kabupaten Bojonegoro**
>
> Aplikasi manajemen pajak daerah berbasis web dan mobile yang menangani pendaftaran wajib pajak, pengelolaan objek pajak, pembuatan billing, penerbitan SKPD, pelaporan pendapatan, serta layanan publik terkait pajak daerah.

> Referensi status stabilisasi dan hasil validasi terbaru tersedia di `docs/QA_STABILIZATION_2026-03-23.md`.

---

## Daftar Isi

1. [Arsitektur Aplikasi](#1-arsitektur-aplikasi)
2. [Peran Pengguna (User Roles)](#2-peran-pengguna-user-roles)
3. [Jenis Pajak yang Didukung](#3-jenis-pajak-yang-didukung)
4. [Perhitungan Pajak per Jenis](#4-perhitungan-pajak-per-jenis)
5. [Alur Aplikasi (Application Flow)](#5-alur-aplikasi-application-flow)
6. [Fitur Backoffice (Admin Panel)](#6-fitur-backoffice-admin-panel)
7. [Fitur Portal Wajib Pajak (Web)](#7-fitur-portal-wajib-pajak-web)
8. [Fitur Aplikasi Mobile (API)](#8-fitur-aplikasi-mobile-api)
9. [Fitur Publik (Tanpa Login)](#9-fitur-publik-tanpa-login)
10. [Dokumen yang Dihasilkan](#10-dokumen-yang-dihasilkan)
11. [Sistem Billing & Pembayaran](#11-sistem-billing--pembayaran)
12. [Manajemen Data Master](#12-manajemen-data-master)
13. [Fitur Reklame (Pajak Iklan)](#13-fitur-reklame-pajak-iklan)
14. [Fitur Air Tanah (Pajak Air Bawah Tanah)](#14-fitur-air-tanah-pajak-air-bawah-tanah)
15. [Fitur Gebyar Sadar Pajak](#15-fitur-gebyar-sadar-pajak)
16. [Sistem Notifikasi](#16-sistem-notifikasi)
17. [Keamanan & Enkripsi Data](#17-keamanan--enkripsi-data)
18. [Integrasi Sistem Lama (Simpadu)](#18-integrasi-sistem-lama-simpadu)
19. [Fitur CMS (Content Management)](#19-fitur-cms-content-management)
20. [Sistem Sanksi & Denda](#20-sistem-sanksi--denda)
21. [Sistem Audit Trail](#21-sistem-audit-trail)
22. [Versi Aplikasi Mobile](#22-versi-aplikasi-mobile)

---

## 1. Arsitektur Aplikasi

### Platform
| Komponen | Teknologi |
|----------|-----------|
| Backend | Laravel (PHP) |
| Admin Panel | Filament (Laravel) |
| Frontend Web | Blade + Livewire |
| Mobile API | RESTful API (Laravel Sanctum) |
| Database | MySQL (enkripsi per-kolom) |
| PDF Generator | DomPDF |

### Tiga Antarmuka Utama

1. **Backoffice (Admin Panel)** — `/admin` — Filament-based, untuk pengelola (admin, verifikator, petugas)
2. **Portal Wajib Pajak (Web)** — `/portal/*` — untuk wajib pajak melalui browser
3. **Aplikasi Mobile (API)** — `/api/v1/*` — untuk wajib pajak melalui aplikasi mobile

### Domain-Driven Structure

```
app/Domain/
├── Auth/          → Autentikasi (User, VerificationCode)
├── WajibPajak/    → Data wajib pajak
├── Tax/           → Pajak self-assessment (billing, pembayaran)
├── Reklame/       → Pajak reklame (SKPD, objek, permohonan)
├── AirTanah/      → Pajak air tanah (SKPD, meter, NPA)
├── Master/        → Data master (jenis pajak, sub jenis, pimpinan)
├── Region/        → Data wilayah (provinsi, kab/kota, kecamatan, desa)
├── CMS/           → Content management (berita, destinasi)
├── Gebyar/        → Program undian berhadiah
├── Simpadu/       → Integrasi sistem lama (read-only)
└── Shared/        → Shared (log, notifikasi, enkripsi, dll)
```

---

## 2. Peran Pengguna (User Roles)

### 2.1 Admin

| Aspek | Detail |
|-------|--------|
| Akses | Backoffice (Filament) |
| Deskripsi | Administrator penuh sistem |
| Hak Khusus | Hak paling luas di backoffice: kelola user backoffice, data master, pembayaran manual, pembatalan pembayaran, create/delete modul admin-only, serta aksi destruktif seperti force delete dan restore sesuai policy/resource |

### 2.2 Verifikator

| Aspek | Detail |
|-------|--------|
| Akses | Backoffice (Filament) |
| Deskripsi | Petugas verifikasi dan pengesahan dokumen |
| Hak Khusus | Verifikasi WP, setujui/tolak SKPD Reklame & Air Tanah (termasuk bulk), verifikasi STPD manual, akses view/list untuk data operasional yang memerlukan pemeriksaan |
| Terikat Pada | Seorang Pimpinan (penandatangan dokumen) |

### 2.3 Petugas

| Aspek | Detail |
|-------|--------|
| Akses | Backoffice (Filament) |
| Deskripsi | Petugas lapangan / operasional |
| Hak Khusus | Buat billing (self-assessment, MBLB, sarang walet), buat SKPD draft, daftarkan WP & objek pajak, proses permohonan sewa reklame, lihat SKPD sendiri |

### 2.4 Wajib Pajak

| Aspek | Detail |
|-------|--------|
| Akses | Portal Web & Aplikasi Mobile |
| Deskripsi | Wajib pajak daerah |
| Hak Khusus | Self-assessment, lapor meter air, perpanjangan reklame, lihat billing/SKPD, pembetulan, gebyar |

### Matriks Akses Halaman Backoffice

Catatan: tabel berikut menggambarkan akses halaman utama. Untuk beberapa modul, hak edit/create lebih sempit daripada hak list/view dan dijelaskan pada bagian detail fitur.

| Halaman / Fitur | Admin | Verifikator | Petugas |
|-----------------|-------|-------------|---------|
| Dashboard | ✅ | ✅ | ✅ |
| Laporan Pendapatan | ✅ | ✅ | ✅ |
| Kelola User Backoffice | ✅ | ❌ | ❌ |
| Data Master (Jenis Pajak, Pimpinan, dll) | ✅ | ❌ | ❌ |
| Harga Patokan (MBLB, Walet, Listrik, Reklame) | ✅ | ❌ | ❌ |
| NPA Air Tanah | ✅ | ❌ | ❌ |
| Kecamatan & Desa | ✅ | ❌ | ❌ |
| Data Wajib Pajak | ✅ | ✅ | ✅ |
| Verifikasi Wajib Pajak | ✅ | ✅ | ❌ |
| Verifikasi SKPD Reklame | ✅ | ✅ | ❌ |
| Verifikasi SKPD Air Tanah | ✅ | ✅ | ❌ |
| Verifikasi Pembetulan | ✅ | ✅ | ❌ |
| Verifikasi Data Change Request | ✅ | ✅ | ❌ |
| Laporan Meter Air | ✅ | ✅ | ✅ |
| Daftarkan WP (Pendaftaran) | ✅ | ❌ | ✅ |
| Daftarkan Objek Pajak | ✅ | ❌ | ✅ |
| Buat Billing Self-Assessment | ✅ | ❌ | ✅ |
| Buat Billing MBLB | ✅ | ❌ | ✅ |
| Buat Billing Sarang Walet | ✅ | ❌ | ✅ |
| Buat SKPD Air Tanah | ✅ | ✅ | ✅ |
| Buat SKPD Reklame | ✅ | ✅ | ✅ |
| Pembatalan Billing | ✅ | ❌ | ✅ |
| Lunas Bayar Manual | ✅ | ❌ | ❌ |
| Pembatalan Pembayaran | ✅ | ❌ | ❌ |
| Daftar SKPD Saya | ❌ | ❌ | ✅ |
| Aset Reklame Pemkab | ✅ (list/view/update) | ✅ (list/view) | ✅ (list/view/update) |
| Permohonan Sewa Reklame | ❌ | ❌ | ✅ |
| Gebyar Sadar Pajak | ✅ | ✅ | ✅ |
| Berita & Destinasi (CMS) | ✅ | ✅ | ✅ |
| Activity Log | ✅ | ✅ | ✅ |

---

## 3. Jenis Pajak yang Didukung

| Kode | Nama Pajak | Singkatan | Tipe Assessment | Tarif Default | Opsen |
|------|-----------|-----------|-----------------|---------------|-------|
| `41101` | Pajak Jasa Perhotelan | Hotel | Self-Assessment | Sesuai tarif | — |
| `41102` | Pajak Makanan dan/atau Minuman | Restoran | Self-Assessment | Sesuai tarif | — |
| `41103` | Pajak Jasa Hiburan | Hiburan | Self-Assessment | Sesuai tarif | — |
| `41104` | Pajak Reklame | Reklame | Official Assessment | 25% | — |
| `41105` | PBJT Tenaga Listrik | PPJ | Official Assessment | Sesuai sub-jenis | — |
| `41106` | Pajak MBLB | MBLB | Official Assessment | 20% | 25% (opsen) |
| `41107` | Pajak Parkir | Parkir | Self-Assessment | Sesuai tarif | — |
| `41108` | Pajak Air Tanah | ABT | Official Assessment | Sesuai tarif | — |
| `41109` | Pajak Sarang Burung Walet | Walet | Self-Assessment (tahunan) | 10% | — |

### Sub Jenis Pajak Penting

| Kode Sub | Jenis Induk | Nama | Keterangan |
|----------|-------------|------|------------|
| `PPJ_SUMBER_LAIN` | PPJ (41105) | PPJ Sumber Lain (PLN) | Pokok pajak diinput langsung |
| `PPJ_DIHASILKAN_SENDIRI` | PPJ (41105) | PPJ Dihasilkan Sendiri | Formula NJTL berbasis kapasitas kVA |
| `MBLB_WAPU` | MBLB (41106) | MBLB Pemungut | Multi-billing per masa pajak |
| *(insidentil)* | Hiburan/Parkir | Event-based | Bebas denda, multi-billing |
| *(Katering/OPD)* | Restoran | OPD/Katering | Bebas denda, multi-billing |

---

## 4. Perhitungan Pajak per Jenis

### 4.1 Self-Assessment Umum (Hotel, Restoran, Hiburan, Parkir)

```
Pajak Terutang = Omzet × (Tarif Persentase / 100)
```

- **Tarif** diambil dari `TarifPajak` berdasarkan `sub_jenis_pajak_id` dan masa pajak (temporal)
- Fallback: objek pajak → jenis pajak tarif_default → 10%
- Omzet diinput oleh wajib pajak (self-assessment) atau petugas

### 4.2 Pajak MBLB (Mineral Bukan Logam dan Batuan)

```
Per jenis mineral:
  Subtotal DPP = Volume × Harga Patokan

Total DPP       = Σ Subtotal DPP (semua jenis mineral)
Pokok Pajak     = round(Total DPP × Tarif Persen / 100)
Opsen           = round(Pokok Pajak × Opsen Persen / 100)
Total Tagihan   = Pokok Pajak + Opsen
```

- **Tarif default**: 20%
- **Opsen**: 25% dari pokok pajak
- **Harga patokan** per mineral bersifat temporal (berlaku mulai/sampai)
- Mendukung **multi-mineral** per billing (satu billing bisa berisi beberapa jenis mineral)
- Sub-jenis `MBLB_WAPU`: dapat multi-billing per masa pajak
- **Portal wajib pajak:** pengajuan MBLB tidak langsung menerbitkan billing code; data mineral dan lampiran masuk ke antrean verifikasi admin/verifikator terlebih dahulu
- **Lampiran portal MBLB:** wajib gambar atau PDF; PDF maksimal 1 MB, gambar otomatis dikompres ke <= 1 MB saat disimpan
- **Prefill masa pajak billing:**
  - `MBLB_WAPU` → selalu prefill **bulan berjalan**
  - `MBLB_WP` → prefill **bulan setelah billing aktif terakhir** berdasarkan `nopd`
  - Jika objek `MBLB_WP` belum punya histori billing aktif pada `nopd` tersebut, fallback ke **bulan berjalan**

### 4.3 Pajak Sarang Burung Walet

```
DPP         = round(Harga Patokan × Volume (kg))
Pokok Pajak = round(DPP × Tarif Persen / 100)
Total       = Pokok Pajak
```

- **Tarif default**: 10%
- **Masa pajak**: tahunan (bukan bulanan)
- **Harga patokan** per jenis sarang (temporal)
- Satu billing = satu jenis sarang

### 4.4 Pajak PPJ — Sumber Lain (PLN)

```
Pokok Pajak = Input langsung (jumlah pajak dari tagihan PLN)
DPP         = round(Pokok Pajak / (Tarif / 100))   ← back-calculated
```

- Pokok pajak dimasukkan langsung oleh petugas
- DPP dihitung mundur dari pokok pajak

### 4.5 Pajak PPJ — Dihasilkan Sendiri (Non-PLN)

```
NJTL (Nilai Jual Tenaga Listrik) = round(
    Kapasitas kVA × (Tingkat Penggunaan % / 100) × Jangka Waktu (jam) × Harga Satuan per kWh
)

DPP         = NJTL
Pokok Pajak = round(DPP × Tarif Persen / 100)
```

- **Harga satuan listrik** per wilayah (temporal)
- Parameter: kapasitas kVA, tingkat penggunaan %, jangka waktu jam

### 4.6 Pajak Reklame

```
POKOK_DASAR        = Tarif Pokok × Luas (m²) × Jumlah Muka × Durasi × Jumlah Reklame
PENYESUAIAN_LOKASI = 0.25 (dalam ruangan) ATAU 1.00 (luar ruangan)
PENYESUAIAN_PRODUK = 1.10 (rokok) ATAU 1.00 (non-rokok)
DASAR_PENGENAAN    = POKOK_DASAR × Penyesuaian Lokasi × Penyesuaian Produk
NILAI_STRATEGIS    = Tarif NS × Durasi × Jumlah Reklame (hanya reklame tetap, ≥10m², per tahun/bulan)
TOTAL_PAJAK        = DASAR_PENGENAAN + NILAI_STRATEGIS
```

**Perhitungan luas berdasarkan bentuk:**

| Bentuk | Rumus |
|--------|-------|
| Persegi | Panjang × Lebar |
| Trapesium | ((Sisi Atas + Sisi Bawah) / 2) × Tinggi |
| Lingkaran | π × (Diameter/2)² |
| Segitiga | (Alas × Tinggi) / 2 |

**Komponen tarif:**
- `Tarif Pokok = (NSPR + NJOPR) × 25%` (sudah termasuk pajak 25%)
- Tarif berbeda per kelompok lokasi jalan (A/A1/A2/A3/B/C)
- Reklame insidentil: tarif tunggal, tanpa kelompok lokasi

**Nilai Strategis:**
- Hanya untuk reklame **tetap** (non-insidentil)
- Hanya untuk luas **≥ 10 m²**
- Hanya untuk satuan waktu **perTahun** atau **perBulan**
- Didapat dari tabel `reklame_nilai_strategis` berdasarkan kelas kelompok (A/B/C) dan range luas

**Penyesuaian khusus:**
- Dalam ruangan: diskon 75% (×0.25)
- Produk rokok: surcharge 10% (×1.10)

**Reklame Sewa Aset Pemkab (Harga Tetap):**

```
Total Pajak = Harga Sewa per Periode × Durasi
```

- Tidak menggunakan formula tarif, langsung dari harga sewa aset

### 4.7 Pajak Air Tanah

```
Usage (m³)       = Meter Akhir - Meter Awal
Dasar Pengenaan  = Usage × NPA per m³ (bertingkat/tiered)
Jumlah Pajak     = Dasar Pengenaan × (Tarif Persen / 100)
```

**NPA (Nilai Perolehan Air) — Sistem Bertingkat:**

NPA per m³ ditentukan berdasarkan:
- **Kelompok Pemakaian** (1–5)
- **Kriteria SDA** (1–4):
  - 1: Air Tanah Kualitas Baik, Ada Sumber Alternatif
  - 2: Air Tanah Kualitas Baik, Tidak Ada Sumber Alternatif
  - 3: Air Tanah Kualitas Tidak Baik, Ada Sumber Alternatif
  - 4: Air Tanah Kualitas Tidak Baik, Tidak Ada Sumber Alternatif

**Contoh tier NPA:**
```json
[
  { "min_vol": 0,   "max_vol": 100,      "npa": 500 },
  { "min_vol": 101, "max_vol": 99999999, "npa": 750 }
]
```

Perhitungan dilakukan per-tier: volume dipecah sesuai bracket dan dikalikan NPA masing-masing tier.

---

## 5. Alur Aplikasi (Application Flow)

### 5.1 Alur Pendaftaran Wajib Pajak

```
[Wajib Pajak]                    [Petugas/Admin]              [Verifikator/Admin]
     |                                 |                              |
     |-- Registrasi via Mobile ------->|                              |
     |   (OTP Email → Verifikasi)      |                              |
     |                                 |                              |
     |   ATAU                          |                              |
     |                                 |-- Daftarkan WP Manual ------>|
     |                                 |   (via Backoffice)           |
     |                                 |                              |
    |                                 |                              |-- Verifikasi WP
    |                                 |                              |   (Setujui/Tolak/
    |                                 |                              |    Perlu Perbaikan)
     |                                 |                              |
     |<----- Notifikasi Status --------|<-----------------------------|
     |   (NPWPD digenerate jika disetujui)                            |
```

**Format NPWPD:** `P1XXXXXXXXXXX` (perorangan) atau `P2XXXXXXXXXXX` (perusahaan) — 13 karakter, sekuensial.

**Status Wajib Pajak:**
- `menungguVerifikasi` → Menunggu Verifikasi
- `disetujui` → Disetujui (NPWPD terbit)
- `ditolak` → Ditolak
- `perluPerbaikan` → Perlu Perbaikan (menunggu perbaikan data dari wajib pajak)

### 5.2 Alur Self-Assessment (PBJT)

```
[Wajib Pajak]                         [Petugas]                    [Sistem]
     |                                      |                          |
     |-- Buat Billing dari Portal/App ----->|                          |
     |   (pilih objek, masukkan omzet)      |                          |
     |                                      |                          |
     |   ATAU                               |                          |
     |                                      |-- Buat Billing -------->|
     |                                      |   (via Backoffice)       |
     |                                      |                          |
     |                                      |                     [Generate Billing Code]
     |                                      |                     [Hitung Pajak]
     |                                      |                     [Hitung Jatuh Tempo]
     |                                      |                     [Hitung Sanksi jika terlambat]
     |                                      |                          |
     |<----- Billing Terbit + Notifikasi ---|<-------------------------|
     |                                      |                          |
     |-- Bayar via Channel Pembayaran ----->|                          |
     |                                      |                          |
     |                              [Status: paid]                     |
    |                              [SPTPD diterbitkan jika triwulan lengkap]
    |                              [STPD otomatis diterbitkan jika triwulan lengkap dan ada sanksi]
```

### 5.3 Alur Pajak Reklame

```
[Wajib Pajak]           [Petugas]              [Verifikator]          [Sistem]
     |                       |                       |                     |
     |-- Ajukan Perpanjangan/|                       |                     |
     |   Permohonan Sewa --->|                       |                     |
     |                       |                       |                     |
     |                       |-- Buat Draft SKPD --->|                     |
     |                       |   (hitung pajak)      |                     |
     |                       |                       |                     |
     |                       |                       |-- Setujui SKPD ---->|
     |                       |                       |   ATAU Tolak        |
     |                       |                       |                     |
     |                       |                       |              [Generate nomor SKPD]
     |                       |                       |              [Generate kode billing]
     |                       |                       |              [Buat record Tax]
     |                       |                       |              [Sync status aset]
     |                       |                       |                     |
     |<----- Notifikasi SKPD Terbit -----------------|<--------------------|
```

**Format Nomor SKPD Reklame:** `SKPD-RKL/{YYYY}/{MM}/{000001}`

### 5.4 Alur Pajak Air Tanah

```
[Wajib Pajak]           [Petugas]              [Verifikator]          [Sistem]
     |                       |                       |                     |
     |-- Lapor Meter ------->|                       |                     |
     |   (foto + GPS)        |                       |                     |
     |                       |                       |                     |
     |                       |-- Proses Laporan ---->|                     |
     |                       |   Buat Draft SKPD     |                     |
     |                       |                       |                     |
     |   ATAU                |                       |                     |
     |                       |-- Buat SKPD langsung->|                     |
     |                       |   (input meter manual) |                    |
     |                       |                       |                     |
     |                       |                       |-- Setujui SKPD ---->|
     |                       |                       |   ATAU Tolak        |
     |                       |                       |                     |
     |                       |                       |             [Generate nomor SKPD]
     |                       |                       |             [Generate kode billing]
     |                       |                       |             [Buat record Tax]
     |                       |                       |             [Update meter terakhir]
     |                       |                       |                     |
     |<----- Notifikasi SKPD Terbit -----------------|<--------------------|
```

**Format Nomor SKPD Air Tanah:** `SKPD-ABT/{YYYY}/{MM}/{000001}`

**Skenario pembuatan SKPD Air Tanah:**
1. **Objek baru** — belum ada riwayat meter, input awal dari 0
2. **Tanpa meter** — input penggunaan langsung dalam m³
3. **Ganti meter** — 4 field: akhir meter lama, awal meter baru, akhir meter baru, catatan
4. **Normal** — meter awal (otomatis dari pembacaan terakhir) + meter akhir

### 5.5 Alur Pembetulan (Koreksi Billing)

```
[Wajib Pajak]                    [Admin/Verifikator]
     |                                 |
     |-- Ajukan Pembetulan ----------->|
     |   (alasan, omzet baru, lampiran)|
     |                                 |
     |                                 |-- Proses
     |                                 |   (jika billing belum bayar → batalkan lama, buat baru)
     |                                 |   (jika billing sudah bayar → buat pembetulan ke-N, kredit pajak)
     |                                 |
     |<----- Notifikasi --------------|
```

- **`pembetulan_ke`**: 0 = original, 1 = pembetulan pertama, dst.
- Pembetulan yang sudah dibayar membentuk rantai (`parent_tax_id` → `children`)
- Kredit pajak = total yang sudah dibayar pada billing sebelumnya

### 5.6 Alur Permohonan Sewa Reklame (Publik)

```
[Publik/WP]                       [Petugas]                 [Verifikator]
     |                                 |                          |
     |-- Pilih Aset Tersedia --------->|                          |
     |   Isi Form + Upload Dokumen     |                          |
     |   (KTP, NPWP, Desain)           |                          |
     |                                 |                          |
     |   Status: DIAJUKAN              |                          |
     |                                 |-- Proses Permohonan ---->|
     |                                 |   (cek NPWPD / buat baru)|
     |                                 |                          |
     |   ATAU                          |-- Perlu Revisi --------->|
     |<-- Revisi Diminta --------------|                          |
     |-- Kirim Revisi ---------------->|                          |
     |                                 |                          |
     |                                 |-- Buat SKPD ----------->|
     |                                 |                          |
     |                                 |                          |-- Setujui
     |                                 |                          |
     |<----- SKPD Terbit (Signed URL)--|<-------------------------|
```

**Format Tiket:** `SEWA-{YYYYMMDD}-{0001}` — sekuensial per hari.

**Status Permohonan:**
- `diajukan` → `diproses` → `disetujui` / `ditolak`
- `diajukan` → `perlu_revisi` → `diajukan` (kembali setelah revisi)

---

## 6. Fitur Backoffice (Admin Panel)

### 6.1 Dashboard

Dashboard menampilkan:
- **Sapaan dinamis** berdasarkan waktu (Selamat Pagi/Siang/Sore/Malam)
- **4 Kartu Statistik:**
  - Pendapatan Bulan Ini (total pajak lunas) + tren terhadap bulan lalu
  - Total Wajib Pajak (status disetujui) + indikator pertambahan bulan ini
  - Billing Pending (transaksi `pending` + `verified` yang belum lunas)
  - Transaksi Bulan Ini (billing lunas bulan berjalan)
- **Blok Perlu Tindakan:** shortcut verifikasi dengan counter untuk Wajib Pajak, Pengajuan Reklame, Permintaan Pembetulan, SKPD Reklame, dan SKPD Air Tanah jika ada item menunggu
- **Chart Line:** Tren pendapatan 6 bulan terakhir
- **Visual pendapatan per jenis pajak:** ringkasan bulan ini per jenis pajak dalam bentuk bar/progress list
- **Daftar:** transaksi terbaru dengan link ke halaman Laporan Pendapatan

### 6.2 Halaman Buat Billing

#### Buat Billing Self-Assessment
- **Jenis pajak:** Hotel (41101), Restoran (41102), Hiburan (41103), PPJ (41105), Parkir (41107)
- **Fitur:** Pencarian NIK/NPWPD/nama, auto-deteksi masa pajak berikutnya, input omzet, deteksi duplikat, dan konfirmasi pembetulan atau penggantian billing sesuai status tagihan yang sudah ada
- **Aturan prefill masa pajak:**
  - Objek `is_opd` atau `is_insidentil` → selalu prefill **bulan berjalan**
  - Objek reguler → prefill **bulan setelah billing aktif terakhir** berdasarkan `nopd`
  - Jika objek reguler belum punya histori billing aktif pada `nopd` tersebut, fallback ke **bulan berjalan**
  - Sarang Walet tetap **tahunan** dan tidak mengikuti logika bulanan ini
- **Sub-flow PPJ Sumber Lain (PLN):** Input pokok pajak langsung
- **Sub-flow PPJ Non-PLN:** Input kapasitas kVA, tingkat penggunaan, jangka waktu, pilih harga satuan listrik

#### Buat Billing MBLB
- **Jenis pajak:** MBLB (41106)
- **Fitur:** Input volume per jenis mineral dari daftar harga patokan aktif, kalkulasi otomatis DPP + opsen
- **Portal WP:** submit sebagai pengajuan verifikasi; billing code baru diterbitkan setelah admin/verifikator menyetujui pengajuan dan meninjau lampiran
- **Aturan prefill masa pajak:**
  - `MBLB_WAPU` → selalu prefill **bulan berjalan**
  - `MBLB_WP` → prefill **bulan setelah billing aktif terakhir** berdasarkan `nopd`
  - Jika objek `MBLB_WP` belum punya histori billing aktif pada `nopd` tersebut, fallback ke **bulan berjalan**

#### Buat Billing Sarang Walet
- **Jenis pajak:** Sarang Burung Walet (41109)
- **Fitur:** Pilih jenis sarang, input volume (kg), masa pajak tahunan

### 6.3 Halaman Buat SKPD

#### Buat SKPD Air Tanah
- **Fitur:** Pilih objek air tanah, 4 skenario meter (baru/tanpa meter/ganti meter/normal), lookup NPA bertingkat (tiered), perhitungan pajak otomatis, notifikasi ke verifikator

#### Buat SKPD Reklame
- **Dua mode:** berbasis objek WP atau aset Pemkab
- **Mode Objek WP:** Pilih sub-jenis pajak (tetap/insidentil), kelompok lokasi, satuan waktu, dimensi, jumlah, lokasi penempatan, jenis produk, perhitungan tarif dinamis + nilai strategis + penyesuaian
- **Mode Aset Pemkab (Simplified):**
  - Step 1: Cari & pilih aset reklame milik Pemkab
  - Step 2: Cari & pilih wajib pajak (penyewa) berdasarkan NPWPD, NIK, atau nama
  - Step 3: Pilih satuan waktu (harga sewa per minggu/bulan/tahun otomatis dari data aset)
  - Step 4: Isi durasi dan masa berlaku mulai
  - Perhitungan: Harga sewa × durasi (tarif tetap, tanpa lookup tarif/penyesuaian)
  - Field yang di-hide: sub jenis pajak, kelompok lokasi, lokasi penempatan, jenis produk, jumlah reklame, luas, jumlah muka
  - Preview: Menampilkan harga sewa per periode, durasi, dan total pajak
- **Permohonan sewa online:** Data WP dan aset diisi otomatis dari permohonan, perhitungan tetap menggunakan metode harga tetap

### 6.4 Verifikasi

| Fitur | Badge Counter | Aksi |
|-------|---------------|------|
| Wajib Pajak | WP menunggu verifikasi | Setujui (generate NPWPD) / Tolak / Perlu Perbaikan |
| SKPD Reklame | SKPD draft | Setujui & Terbitkan / Tolak / Bulk Approve / Bulk Reject |
| SKPD Air Tanah | SKPD draft | Setujui & Terbitkan / Tolak / Bulk Approve / Bulk Reject |
| Surat Ketetapan Pajak | Draft ketetapan | Setujui & Terbitkan / Tolak / Kompensasikan SKPDLB |
| Pembetulan | Permintaan pending | Proses → Setujui & Buat Billing / Tolak |
| Laporan Meter | Laporan submitted | Proses → Buat Draft SKPD |
| Data Change Request | Request pending | Setujui (apply changes) / Tolak |

Catatan implementasi saat ini:
- Edit data identitas Wajib Pajak oleh admin/petugas tidak langsung mengubah entity, tetapi membuat `DataChangeRequest` berstatus `pending`.
- Verifikator/admin kemudian mereview request tersebut dari modul `Data Change Request` untuk `approve` atau `reject`.
- Edit record Wajib Pajak yang sudah `disetujui` tetap tersedia untuk admin/petugas, tetapi perubahan sensitifnya tetap masuk ke workflow `DataChangeRequest` agar ada jejak review.
- Aksi verifikasi Wajib Pajak (`Setujui`, `Tolak`, `Perlu Perbaikan`) digunakan untuk record berstatus `menungguVerifikasi` dan dijalankan oleh admin/verifikator.

### 6.11 Surat Ketetapan Pajak Daerah
- **Akses list:** Admin, Verifikator, Petugas
- **Akses create/edit draft:** Admin, Petugas
- **Akses approve/reject:** Admin, Verifikator
- **Navigasi:** Verifikasi → Surat Ketetapan
- **Jenis dokumen:** `SKPDKB`, `SKPDKBT`, `SKPDLB`, `SKPDN`
- **Flow draft:** Pilih billing sumber → pilih jenis surat → pilih dasar penerbitan → isi nominal dasar dan bulan bunga → simpan draft
- **Flow approve:** Verifikator menerbitkan nomor dokumen resmi dan menetapkan pimpinan penandatangan
- **Nomor dokumen:** Menggunakan format `{TIPE}/{YYYY}/{MM}/{000001}`
- **Kode billing penagihan:** Hanya `SKPDKB` dan `SKPDKBT` yang membentuk billing turunan baru pada tabel `taxes`; billing ini tetap 18 digit, tetapi digit ke-17 dan ke-18 ditetapkan menjadi `19`
- **Tanpa billing baru:** `SKPDLB` tidak membentuk billing penagihan baru karena menghasilkan saldo kredit, sedangkan `SKPDN` tidak membentuk billing penagihan baru karena bersifat nihil
- **Flow kompensasi:** `SKPDLB` yang sudah disetujui menyimpan saldo kredit dan dapat dialokasikan ke billing lain milik wajib pajak yang sama
- **Dokumen:** Route `/surat-ketetapan/{letterId}/view` dan `/surat-ketetapan/{letterId}/download`, dengan output PDF ukuran F4
- **Kontrol akses dokumen:** PDF hanya untuk role backoffice atau wajib pajak pemilik billing sumber

### 6.5 Pembatalan Billing
- **Akses:** Admin dan petugas
- **Tab aktif:** Daftar billing self-assessment aktif → aksi Batalkan (with reason)
- **Tab dibatalkan:** Daftar billing yang sudah dibatalkan → aksi Pulihkan (restore)
- Soft delete — data tidak hilang permanen

### 6.6 Lunas Bayar Manual
- **Akses:** Admin only
- **Flow:** Cari billing by kode/NPWPD → input jumlah pokok + sanksi + tanggal bayar + lokasi + referensi + bukti (upload)
- **Deteksi otomatis:** Lebih bayar, kurang bayar (pembayaran parsial), atau tepat
- **Hasil:** Buat record `TaxPayment`, update status ke `paid`/`partially_paid`
- **Observer/model otomatis:** `sptpd_number` diterbitkan saat billing menjadi `paid` dan syarat `isTriwulanComplete()` terpenuhi; `stpd_number` otomatis diterbitkan saat billing memiliki sanksi dan syarat `isTriwulanComplete()` juga sudah terpenuhi
- **Catatan STPD:** Billing `partially_paid` tidak lagi menerbitkan STPD otomatis sebelum triwulan lengkap; untuk kebutuhan penagihan sebelum itu gunakan flow STPD manual

### 6.7 Pembatalan Pembayaran
- **Akses:** Admin only
- **Flow:** Cari billing → lihat daftar pembayaran → pilih dan batalkan (with reason)
- **Efek:** Soft-delete pembayaran, recalculate sisa tagihan, revoke SPTPD jika tidak lagi lunas, revoke STPD jika pokok tidak lagi lunas

### 6.8 Daftar SKPD Saya (Petugas)
- **Akses:** Petugas only
- **Tab:** Air Tanah / Reklame
- **Fitur:** Lihat semua SKPD yang dibuat petugas bersangkutan, cetak/unduh PDF, revisi & ajukan ulang (jika ditolak)

### 6.9 Laporan Pendapatan
- **View tahun:** Ringkasan semua tahun (2019–sekarang) — total transaksi, total pendapatan, pending
- **View per tahun:** Detail per jenis pajak — total transaksi, total pendapatan, pendapatan bulan ini, pending

### 6.10 Buat STPD Manual (Petugas)
- **Akses:** Admin, Petugas, Verifikator
- **Navigasi:** Laporan Petugas → Buat STPD
- **Flow:** Cari billing by kode (18 digit) atau NPWPD (13 digit) → Pilih tipe STPD → Isi parameter → Buat draft
- **Tipe STPD:**
  - **Pokok & Sanksi:** Billing belum dibayar sama sekali. Input proyeksi tanggal bayar → auto-hitung bulan terlambat + sanksi.
  - **Sanksi Saja:** Pokok sudah lunas, sanksi belum terbayar. Sanksi diambil dari data billing.
- **Validasi:** Cek duplikasi (1 billing tidak dapat memiliki lebih dari 1 STPD manual berstatus `draft` atau `disetujui`), cek status billing valid, cek OPD/insidentil
- **Hasil:** Buat record `stpd_manuals` status draft, notifikasi verifikator

### 6.10.1 Verifikasi STPD (Verifikator)
- **Akses:** Admin, Verifikator
- **Navigasi:** Verifikasi → Verifikasi STPD (badge count draft)
- **Tabel:** Tgl buat, kode billing, tipe, sanksi, bulan terlambat, proyeksi bayar, status, petugas
- **Filter:** Status, Tipe
- **Aksi individual:** Detail (modal), Setujui & Terbitkan, Tolak (dengan alasan)
- **Aksi bulk:** Setujui Terpilih, Tolak Terpilih
- **On Approve:** Generate nomor STPD resmi (format `STPD/{YYYY}/{MM}/{000001}`), sync `stpd_number` + `sanksi` ke tabel `taxes`, set pimpinan
- **Dokumen:** Cetak/Unduh PDF STPD (route `/stpd-manual/{stpdId}/view` dan `/stpd-manual/{stpdId}/download`) hanya untuk pemilik tax terkait atau role backoffice (`admin`, `verifikator`, `petugas`) setelah status `disetujui`

### 6.12 Manajemen Transaksi (TaxResource)
- **Fitur:** Daftar semua transaksi pajak
- **Dua mode:** Self-Assessment atau Official Assessment (kolom berbeda)
- **Aksi dokumen:**
  - Mode self-assessment: Cetak/Unduh Billing, SPTPD, dan STPD sesuai ketersediaan nomor dokumen
  - Mode official assessment: Cetak/Unduh SKPD Reklame atau SKPD Air Tanah berdasarkan billing terkait
- **Export:** Copy data (TSV ke clipboard) dan Export CSV
- **Cakupan export/copy:** Menggunakan data hasil filter aktif pada tabel, sehingga operator bisa menyalin atau mengunduh subset laporan tanpa export seluruh transaksi.
- **Akses:** Tersedia untuk semua role backoffice (`admin`, `verifikator`, `petugas`) pada halaman Laporan Pendapatan.
- **Status laporan:** Nilai status di output copy/export mengikuti label tampilan transaksi, termasuk untuk status yang disimpan sebagai enum.

### 6.13 Pendaftaran
- **Data Wajib Pajak:** List dan detail dapat diakses semua role backoffice untuk kebutuhan operasional dan verifikasi
- **Daftar Wajib Pajak:** Pendaftaran WP baru hanya untuk admin dan petugas, dengan form perorangan/perusahaan serta cascading wilayah
- **Objek Pajak:** Pendaftaran objek pajak baru hanya untuk admin dan petugas, dengan form kondisional per jenis pajak (reklame: bentuk+dimensi, air tanah: kelompok+kriteria, dll)

---

## 7. Fitur Portal Wajib Pajak (Web)

### 7.1 Dashboard Portal
- Total tagihan pending (IDR)
- Total sudah dibayar (IDR)
- Jumlah objek pajak aktif
- Transaksi terbaru (5 terakhir)
- Kupon undian Gebyar

### 7.2 Self-Assessment (Wizard 4 Langkah)
- **Route utama:** `/portal/self-assessment`
- **Route form per jenis pajak:** `/portal/self-assessment/{jenisPajakId}/create`
- **Route hasil sukses:** `/portal/self-assessment/{taxId}/success`
- **Route hasil sukses pengajuan MBLB:** `/portal/self-assessment/mblb-submissions/{submissionId}/success`
1. **Pilih Jenis Pajak** — daftar pajak self-assessment yang dimiliki WP
2. **Isi Formulir:**
  - Pilih objek pajak
  - Prefill masa pajak mengikuti aturan objek:
    - objek `is_opd`, `is_insidentil`, dan `MBLB_WAPU` → **bulan berjalan**
    - objek reguler, termasuk `MBLB_WP`, Hotel, Restoran non-OPD, Hiburan non-insidentil, PPJ, dan Parkir → **bulan setelah billing aktif terakhir** berdasarkan `nopd`
    - jika objek reguler belum punya histori billing aktif pada `nopd` tersebut, user dapat memilih manual; untuk Sarang Walet bentuknya **tahun**
  - Step data perhitungan bersifat **type-aware** tanpa mengubah urutan wizard:
    - Hotel, Restoran, Hiburan, Parkir → input omzet
    - PPJ `PPJ_SUMBER_LAIN` → input pokok pajak langsung, DPP dihitung mundur dari tarif
    - PPJ `PPJ_DIHASILKAN_SENDIRI` → input kapasitas kVA, tingkat penggunaan, jangka waktu jam, dan harga satuan listrik aktif
    - Sarang Walet → pilih jenis sarang dan input volume (kg)
    - MBLB → input volume per mineral aktif, lalu submit sebagai pengajuan verifikasi
  - Upload lampiran wajib pada flow portal
3. **Sistem menghitung:**
  - Lookup tarif berlaku
  - Hitung pajak terutang sesuai tipe form yang dipilih
  - Deteksi duplikat billing pada masa pajak yang sama
  - Generate kode billing 18 digit untuk flow standard, PPJ, dan Sarang Walet
  - Khusus MBLB portal: simpan submission menunggu verifikasi admin/verifikator sebelum billing diterbitkan
  - Hitung jatuh tempo
4. **Halaman sukses** — tampilkan billing code + detail

### 7.3 Pembetulan
- Ajukan pembetulan untuk billing yang sudah ada (pending/paid/verified)
- Form: alasan, omzet baru, lampiran (opsional)
- Guard pengajuan: satu billing tidak boleh memiliki lebih dari satu `PembetulanRequest` berstatus `pending`
- Lampiran portal bersifat opsional, menerima JPG/PNG/PDF maksimal 1 MB
- Review pembetulan dilakukan dari backoffice oleh admin/verifikator sampai menghasilkan billing pengganti atau penolakan

### 7.4 Riwayat Transaksi
- Daftar semua transaksi pajak WP
- Filter: status transaksi sesuai status billing yang tersedia di sistem (mis. `pending`, `verified`, `paid`)
- Pencarian: kode billing, jenis pajak
- Urutan data: terbaru berdasarkan `created_at`
- Pagination portal mendukung `per_page` `10`, `15`, `25`, atau `50`

### 7.5 Air Tanah Hub
- **Objek Saya:** Daftar objek air tanah beserta laporan meter terakhir
- **SKPD:** Tab `proses` memuat status `draft` dan `menungguVerifikasi`; tab `selesai` memuat status `disetujui` dan `ditolak`
- **Detail SKPD:** Info lengkap per SKPD
- **Batasan portal:** Pendaftaran objek air tanah baru dan pelaporan meter dilakukan melalui aplikasi mobile, sementara portal web berfungsi untuk monitoring objek dan dokumen SKPD

### 7.6 Reklame Hub
- **Dashboard:** Total objek, aktif, kadaluarsa, SKPD terbit, permohonan aktif
- **Objek Saya:** Daftar objek reklame + detail, riwayat pengajuan, dan ringkasan hingga 3 SKPD terbaru
- **Perpanjangan:** Form perpanjangan (30/90/180/365 hari) — hanya jika ≤30 hari dari kadaluarsa atau sudah kadaluarsa, dan tidak ada pengajuan aktif berstatus `diajukan`, `menungguVerifikasi`, atau `diproses`
- **SKPD:** Tab `proses` memuat status `draft` dan `menungguVerifikasi`; tab `selesai` memuat status `disetujui` dan `ditolak`
- **Batasan portal:** Pendaftaran objek reklame baru tidak tersedia di portal; WP diarahkan ke aplikasi mobile atau kantor Bapenda. Portal dipakai untuk melihat objek yang sudah ada, mengajukan perpanjangan, dan memantau SKPD

### 7.7 Cek Billing
- Input kode billing → tampilkan detail, jumlah, dan status pembayaran

### 7.8 Dokumen
Wajib pajak dapat melihat dan mengunduh:
- Billing document (self-assessment)
- SPTPD (jika `sptpd_number` sudah terbit)
- STPD (jika `stpd_number` sudah tersedia)
- Status billing per transaksi melalui route `/portal/billing/{taxId}/status`
- SKPD Air Tanah dilihat dari hub air tanah, sedangkan SKPD Reklame dilihat dari hub reklame sesuai daftar milik WP
- Guard akses: route billing/SPTPD/STPD hanya dapat diakses oleh pemilik billing yang sedang login; role backoffice (`admin`, `verifikator`, `petugas`) tetap dapat membuka dokumen yang sama dari sesi autentik backoffice
- Guard akses SKPD autentik: route `/skpd-reklame/*` dan `/skpd-air-tanah/*` hanya dapat diakses oleh pemilik objek/SKPD yang sedang login; role backoffice (`admin`, `verifikator`, `petugas`) tetap dapat membuka dokumen tersebut dari sesi autentik backoffice

### 7.9 Notifikasi
- Bell icon dengan badge unread count
- Daftar semua notifikasi (judul, isi, waktu)
- Mark as read satu per satu atau semua
- Endpoint portal yang dipakai UI: `/portal/notifications`, `/portal/notifications/unread-count`, `/portal/notifications/{id}/read`, `/portal/notifications/read-all`
- Logout portal menggunakan `POST /logout`

---

## 8. Fitur Aplikasi Mobile (API)

### 8.1 Autentikasi
| Fitur | Detail |
|-------|--------|
| Registrasi | OTP via email → verifikasi → isi data lengkap → Sanctum token |
| Login | Email atau NIK + password → Sanctum token |
| PIN | Set & verifikasi PIN 6 digit untuk operasi sensitif |
| Profil | Lihat & update profil, ubah password |
| Keamanan | API memeriksa status lock user jika sudah terkunci; lockout 5 kali gagal login saat ini ditegakkan penuh pada portal web |

**Kontrak penting:**
- `POST /api/v1/auth/request-otp` mewajibkan `email` dan `no_whatsapp`; OTP berlaku 30 detik, cooldown 2 menit, maksimal 3 request per 15 menit
- `POST /api/v1/auth/verify-otp` memakai `otp_id` + `code` 6 digit; maksimal 3 percobaan gagal sebelum user harus meminta OTP baru
- `POST /api/v1/register` membutuhkan `verification_token` hasil verifikasi OTP serta data identitas lengkap, termasuk kode wilayah dan `password_confirmation`; user baru dibuat dengan role default `user`
- `POST /api/v1/login` memakai field `email`, tetapi nilainya boleh berupa email atau NIK
- Endpoint profil terproteksi Sanctum: `profile`, `update-profile`, `update-password`, `update-pin`, `verify-pin`, `logout`

### 8.2 Self-Assessment
- **Create:** Pilih objek pajak → input periode bulan/tahun + omzet → generate billing, dengan lampiran opsional
- **History:** Daftar semua transaksi
- **Payload create:** `tax_object_id`, `periode_bulan`, `periode_tahun`, `omzet`, `attachment?`
- **Validasi utama:** objek harus ada dan dimiliki user login; periode bulan 1-12; lampiran opsional berupa image maksimal 2 MB
- **Billing check publik:** `GET /api/v1/billing/check?code=...`

### 8.3 Air Tanah (Water Tax)
- **Daftarkan objek:** Registrasi sumber air baru
- **Lapor meter:** Upload foto + lokasi GPS + angka meter sebelum/sesudah
- **Riwayat laporan:** Lihat status laporan meter
- **Urutan history:** `GET /api/v1/water-reports/history` diurutkan terbaru berdasarkan `reported_at` dan memuat relasi minimal objek air tanah
- **Register object payload:** `nama_objek`, `alamat_objek`, `kecamatan`, `kelurahan`, `jenis_sumber`, `latitude?`, `longitude?`, `foto_objek?`
- **Jenis sumber yang diterima:** `sumurBor`, `sumurGali`, `matAir`, `springWell`
- **Validasi register:** `foto_objek` opsional berupa image maksimal 2 MB; latitude/longitude opsional tetapi harus numerik jika dikirim
- **Submit report payload:** `tax_object_id`, `meter_reading_before`, `meter_reading_after`, `foto_meter`, `latitude`, `longitude`
- **Validasi laporan:** `meter_reading_after` harus lebih besar atau sama dengan `meter_reading_before`; `foto_meter` wajib image maksimal 2 MB
- **Guard kepemilikan:** laporan meter hanya dapat dikirim untuk objek air tanah aktif milik user login; objek di luar kepemilikan atau nonaktif akan `404`
- **Status utama laporan:** `submitted`, `processing`, `approved`, `rejected`
- **Efek submit report:** sistem menyimpan foto ke storage publik, menandai laporan `submitted`, memperbarui meter terakhir objek, dan mengirim notifikasi ke role `petugas`

### 8.4 Reklame
- **Objek saya:** Daftar objek reklame aktif
- **Perpanjangan:** Ajukan perpanjangan izin reklame
- **Riwayat permohonan:** Status pengajuan
- **Extension payload:** `tax_object_id`, `durasi_perpanjangan_hari`, `catatan_pengajuan?`
- **Guard eligibility:** perpanjangan hanya boleh untuk objek milik user login yang sudah kedaluwarsa atau sisa masa berlakunya maksimal 30 hari
- **Durasi yang diterima:** `30`, `90`, `180`, `365` hari
- **Guard pengajuan aktif:** API menolak pengajuan baru jika masih ada request berstatus `diajukan`, `menungguVerifikasi`, atau `diproses`
- **Riwayat pengajuan:** `GET /api/v1/reklame-requests` mengembalikan request user beserta relasi minimal objek reklame, diurutkan terbaru berdasarkan `tanggal_pengajuan`
- **Status utama pengajuan:** `diajukan`, `menungguVerifikasi`, `diproses`, `disetujui`, `ditolak`
- **Aset Pemkab:** `GET /api/v1/reklame-aset-pemkab` mendukung filter query `jenis` dan `status`

### 8.5 Sewa Reklame
- **Lihat aset:** Daftar aset reklame milik Pemkab yang tersedia
- **Ajukan sewa:** Upload KTP dan desain reklame, dengan NPWP opsional, lalu pilih durasi
- **Riwayat sewa:** Daftar permohonan sewa
- **Urutan history:** `GET /api/v1/reklame-sewa` diurutkan terbaru berdasarkan `created_at` dan memuat relasi minimal aset reklame
- **Submit payload:** `aset_reklame_pemkab_id`, `jenis_reklame_dipasang`, `durasi_sewa_hari`, `tanggal_mulai_diinginkan`, `nomor_registrasi_izin`, `file_ktp`, `file_desain_reklame`, dengan `email`, `catatan`, `file_npwp` opsional
- **Validasi utama:** aset harus aktif dan `tersedia`; `durasi_sewa_hari` antara `1` sampai `3650`; `tanggal_mulai_diinginkan` minimal hari ini; satu user tidak boleh memiliki permohonan aktif ganda pada aset yang sama
- **Validasi file:** `file_ktp` dan `file_npwp` menerima `jpg/jpeg/png/pdf` maksimal 2 MB; `file_desain_reklame` menerima `jpg/jpeg/png/pdf` maksimal 5 MB
- **Status pengajuan aktif yang diblok:** `diajukan`, `perlu_revisi`, `diproses`
- **Status utama pengajuan:** `diajukan`, `perlu_revisi`, `diproses`, `disetujui`, `ditolak`
- **Nomor tiket:** dibuat otomatis saat permohonan dibuat dengan format `SEWA-YYYYMMDD-####`
- **Efek submit:** lampiran disimpan ke storage lokal dan notifikasi dikirim ke role `petugas`

### 8.6 Gebyar Sadar Pajak
- **Submit:** Upload struk/bukti bayar pajak + jumlah transaksi → dapatkan kupon undian
- **History:** Riwayat submission
- **Urutan history:** `GET /api/v1/gebyar/history` diurutkan terbaru berdasarkan `created_at` dan memuat relasi minimal `jenisPajak`
- **Submit payload:** `jenis_pajak_id`, `place_name`, `transaction_date`, `transaction_amount`, `image`
- **Validasi submit:** `transaction_amount` minimal `1`; `image` wajib image maksimal 2 MB
- **Status utama submission:** `pending`, `approved`, `rejected`
- **Efek submit:** submission dibuat dengan status awal `pending`, default `kupon_count = 1`, lalu admin menerima notifikasi review

### 8.7 Cek Billing (Publik)
- Cek status billing berdasarkan kode billing tanpa login
- Query parameter yang dipakai: `code`
- Jika `code` kosong maka API mengembalikan error `400`
- Respons sukses memuat data billing beserta relasi minimal `jenisPajak` dan `subJenisPajak`; jika kode tidak ada maka API mengembalikan error `404`

### 8.8 Notifikasi
- Daftar notifikasi (paginated)
- Hitung unread
- Mark as read (satu atau semua)
- `GET /api/v1/notifications` dipaginasi 20 item per halaman
- `GET /api/v1/notifications/unread-count` mengembalikan `{ unread_count }`
- `POST /api/v1/notifications/{id}/read` hanya dapat mengubah notifikasi milik user login; notifikasi di luar kepemilikan user akan `404`
- `POST /api/v1/notifications/read-all` menandai semua notifikasi unread milik user login sebagai sudah dibaca

### 8.9 Data Master (Publik)
- Provinsi, Kabupaten/Kota, Kecamatan, Desa
- Jenis pajak aktif
- `master/regencies` mendukung filter `province_code`
- `master/districts` mendukung filter `regency_code`
- `master/villages/{district}` akan `404` jika tidak ada desa untuk kode kecamatan tersebut
- Semua daftar wilayah diurutkan berdasarkan nama; endpoint villages juga mengembalikan `postal_code`
- `master/tax-types` hanya mengembalikan jenis pajak aktif, diurutkan berdasarkan `urutan`

### 8.10 Format Response API
- Semua endpoint API v1 memakai envelope JSON dasar:
  - sukses: `success`, `data`, `message`
  - error: `success`, `message`, dan `data` opsional untuk detail validasi
- Error validasi umumnya memakai status `400`
- Error autentikasi/otorisasi yang muncul di controller saat ini mencakup `401`, `404`, `410`, `422`, dan `429` sesuai kasus endpoint

---

## 9. Fitur Publik (Tanpa Login)

### 9.1 Halaman Informasi

| Halaman | URL | Deskripsi |
|---------|-----|-----------|
| Landing Page | `/` | Halaman utama |
| Login Portal | `/login` | Form login portal wajib pajak |
| Cek Billing | `/cek-billing` | Cek status billing berdasarkan kode |
| Produk Hukum | `/produk-hukum` | Daftar regulasi (Perda, Perbup, UU) |
| Berita | `/berita` | Daftar berita (filter kategori, paginated) |
| Detail Berita | `/berita/{slug}` | Detail berita + view counter |
| Destinasi | `/destinasi` | Daftar destinasi wisata/kuliner |
| Detail Destinasi | `/destinasi/{slug}` | Detail destinasi |

### 9.2 Kalkulator Publik

| Kalkulator | URL | Fungsi |
|------------|-----|--------|
| Kalkulator Sanksi | `/kalkulator-sanksi` | Hitung denda keterlambatan pajak |
| Kalkulator Air Tanah | `/kalkulator-air-tanah` | Estimasi pajak air tanah |
| Kalkulator Reklame | `/kalkulator-reklame` | Estimasi pajak reklame (tarif dinamis, kelompok lokasi, nilai strategis) |

### 9.3 Sewa Reklame (Publik — Tanpa Login)

| Fitur | URL | Deskripsi |
|-------|-----|-----------|
| Info & Peta | `/sewa-reklame` | Daftar aset reklame Pemkab tersedia + peta |
| Ajukan Sewa | `/sewa-reklame/ajukan/{asetId}` | Form permohonan sewa (NIK, file KTP dan desain wajib, NPWP opsional) |
| Cek Tiket | `/sewa-reklame/cek` | Cek status permohonan by nomor tiket |
| Detail Permohonan | `/sewa-reklame/detail/{nomorTiket}` | Detail status permohonan |
| Revisi | `/sewa-reklame/edit/{nomorTiket}` | Edit permohonan jika diminta revisi |
| Cetak SKPD | `/sewa-reklame/skpd/{skpdId}/cetak` | Lihat SKPD (signed URL, tanpa login) |
| Unduh SKPD | `/sewa-reklame/skpd/{skpdId}/unduh` | Download SKPD (signed URL) |

---

## 10. Dokumen yang Dihasilkan

### 10.1 Billing Self-Assessment

| Aspek | Detail |
|-------|--------|
| Nama | Billing / Tagihan Pajak |
| Template | `documents.billing-sa` |
| Ukuran | F4 Folio (609.449 × 935.433 pt) |
| Konten | Data WP, objek pajak, masa pajak, kode billing, DPP, pokok pajak, sanksi, total tagihan, jatuh tempo |
| Konten Khusus | Detail MBLB (mineral per item), Sarang Walet (jenis + volume), PPJ (detail kapasitas) |
| Akses | Portal WP, Backoffice (cetak/unduh) |
| Route Status | `/portal/billing/{taxId}/status` mengarahkan ke SPTPD jika billing sudah lunas dan `sptpd_number` tersedia; selain itu tetap ke billing document |

### 10.2 SPTPD (Surat Pemberitahuan Pajak Terutang Daerah)

| Aspek | Detail |
|-------|--------|
| Nama | SPTPD |
| Template | `documents.sptpd` |
| Kondisi Terbit | Billing berstatus `paid` dan `sptpd_number` diterbitkan setelah syarat `isTriwulanComplete()` terpenuhi |
| Penomoran | `sptpd_number` = `billing_code` (auto-set saat status → paid) |
| Konten | Data lengkap billing + konfirmasi pelunasan |
| Akses | Portal WP pemilik billing (setelah lunas), Backoffice |
| Validasi Akses | Route view/download mengembalikan `404` jika `sptpd_number` belum tersedia |

### 10.3 STPD (Surat Tagihan Pajak Daerah)

| Aspek | Detail |
|-------|--------|
| Nama | STPD |
| Template | `documents.stpd` |
| Kondisi Terbit (Otomatis) | Billing berstatus `paid`, ada sanksi > 0, bukan OPD/insidentil, dan syarat `isTriwulanComplete()` terpenuhi |
| Kondisi Terbit (Manual) | Petugas membuat draft STPD → Verifikator menyetujui → Nomor STPD resmi diterbitkan |
| Nomor Dokumen (Otomatis) | `stpd_number` diisi dari `billing_code` billing asal setelah syarat STPD otomatis terpenuhi |
| Nomor Dokumen (Manual) | `STPD/{YYYY}/{MM}/{000001}` (generate saat verifikator menyetujui) |
| Kode Billing Penagihan (Otomatis) | Tetap menggunakan `billing_code` billing asal, dan kode tersebut menjadi kode pembayaran STPD |
| Kode Billing Penagihan (Manual `pokok_sanksi`) | Tetap menggunakan `billing_code` billing asal sebagai kode pembayaran karena pokok pajak belum dilunasi |
| Kode Billing Penagihan (Manual `sanksi_saja`) | Menggunakan kode billing penagihan khusus STPD yang dibentuk dari billing asal dengan mempertahankan 7 digit pertama, lalu mengubah digit ke-8 dan ke-9 dari `00` menjadi `77` |
| Konten | Data billing + detail sanksi/denda + data pimpinan penandatangan |
| Keterangan Khusus | Tipe **pokok_sanksi**: "Terdapat pokok pajak yang belum dibayar sebesar Rp X beserta sanksi administratif sebesar Rp Y (proyeksi s.d. tanggal Z)" |
| | Tipe **sanksi_saja**: "Terdapat sanksi administratif yang belum terbayarkan sebesar Rp X" |
| Akses | Portal WP pemilik billing dan Backoffice melalui jalur dokumen autentik; route view/download akan `404` jika `stpd_number` belum tersedia |

### 10.3.1 STPD Manual (Buat oleh Petugas)

| Aspek | Detail |
|-------|--------|
| Tabel | `stpd_manuals` |
| Model | `App\Domain\Tax\Models\StpdManual` |
| Tipe | `pokok_sanksi` (billing belum dibayar + proyeksi tanggal bayar) / `sanksi_saja` (pokok lunas, sanksi belum) |
| Workflow | Petugas buat draft → Verifikator setujui/tolak |
| Halaman Petugas | `BuatStpd` (navigasi: Laporan Petugas, sort 6) |
| Halaman Verifikasi | `StpdManualResource` (navigasi: Verifikasi, sort 5, badge count draft) |
| On Approve | Generate nomor STPD resmi, sync `stpd_number` + `sanksi` ke tabel `taxes`, lalu tetapkan kode pembayaran sesuai tipe STPD manual |
| Kode Pembayaran `pokok_sanksi` | Tetap memakai `billing_code` billing asal |
| Kode Pembayaran `sanksi_saja` | Memakai `stpd_payment_code` turunan dari billing asal dengan pola perubahan digit ke-8 dan ke-9 dari `00` menjadi `77` |
| Dokumen | Route `/stpd-manual/{stpdId}/view` (cetak) dan `/stpd-manual/{stpdId}/download` (unduh PDF) |
| Controller | `StpdManualDocumentController` |
| Validasi Akses | PDF hanya bisa diakses jika status STPD manual sudah `disetujui`; draft/ditolak akan `404`, dan route autentik dibatasi ke pemilik tax terkait atau role backoffice (`admin`, `verifikator`, `petugas`) |

### 10.4 SKPD Reklame (Surat Ketetapan Pajak Daerah — Reklame)

| Aspek | Detail |
|-------|--------|
| Nama | SKPD Reklame |
| Template | `documents.skpd-reklame` |
| Format Nomor | `SKPD-RKL/{YYYY}/{MM}/{000001}` |
| Konten | Data WP, objek reklame, jenis reklame, dimensi, lokasi, tarif, dasar pengenaan, nilai strategis, total pajak, masa berlaku, jatuh tempo, tanda tangan elektronik, QR code |
| Akses | Backoffice, Portal WP pemilik SKPD (SKPD Reklame list), Publik (signed URL untuk permohonan sewa) |
| Akses Publik | Signed URL publik hanya untuk SKPD permohonan sewa reklame yang sudah `disetujui` dan memiliki `permohonan_sewa_id` |

### 10.5 SKPD Air Tanah (Surat Ketetapan Pajak Daerah — Air Tanah)

| Aspek | Detail |
|-------|--------|
| Nama | SKPD Air Tanah |
| Template | `documents.skpd-air-tanah` |
| Format Nomor | `SKPD-ABT/{YYYY}/{MM}/{000001}` |
| Konten | Data WP, objek air tanah, meter reading, usage, NPA/tarif per m³, dasar pengenaan, tarif %, jumlah pajak, periode, jatuh tempo, tanda tangan elektronik, QR code |
| Akses | Backoffice, Portal WP pemilik SKPD (SKPD Air Tanah list) |
| Catatan Akses | Tidak ada route publik signed URL; akses PDF hanya melalui jalur autentikasi dan dibatasi ke pemilik objek/SKPD atau role backoffice |

### 10.6 Surat Ketetapan Pajak Daerah Umum

| Aspek | Detail |
|-------|--------|
| Nama | `SKPDKB`, `SKPDKBT`, `SKPDLB`, `SKPDN` |
| Template | `documents.tax-assessment-letter` |
| Ukuran | F4 Folio (609.449 × 935.433 pt) |
| Nomor Dokumen | `{TIPE}/{YYYY}/{MM}/{000001}` |
| Workflow | Draft oleh admin/petugas → review admin/verifikator → terbit resmi |
| Kode Billing Penagihan `SKPDKB` | Membentuk billing turunan baru (`generated_tax_id`) dengan kode billing 18 digit; digit ke-17 dan ke-18 selalu `19` |
| Kode Billing Penagihan `SKPDKBT` | Membentuk billing turunan baru (`generated_tax_id`) dengan kode billing 18 digit; digit ke-17 dan ke-18 selalu `19` |
| Kode Billing Penagihan `SKPDLB` | Tidak membentuk billing baru; hasilnya menjadi saldo kredit yang dapat dikompensasikan |
| Kode Billing Penagihan `SKPDN` | Tidak membentuk billing baru karena dokumen bersifat nihil |
| Relasi ke Billing Sumber | Seluruh surat ketetapan tetap merujuk ke billing sumber sebagai dasar pemeriksaan dan perhitungan |
| Akses | Backoffice dan portal wajib pajak pemilik billing sumber melalui route autentik |
| Catatan | Draft atau dokumen yang ditolak tidak dapat diunduh; route akan `404` sebelum status `disetujui` |

**Contoh pembedaan nomor dokumen dan kode billing penagihan:**
- **STPD Otomatis:** nomor dokumen `352210200026000102`; kode billing penagihan `352210200026000102`
- **STPD Manual `pokok_sanksi`:** nomor dokumen `STPD/2026/03/000001`; kode billing penagihan tetap `352210200026000102`
- **STPD Manual `sanksi_saja`:** nomor dokumen `STPD/2026/03/000002`; kode billing penagihan baru misalnya `352210277026000102`
- **SKPDKB:** nomor dokumen `SKPDKB/2026/03/000001`; kode billing penagihan baru misalnya `352210200026000219`
- **SKPDKBT:** nomor dokumen `SKPDKBT/2026/03/000001`; kode billing penagihan baru misalnya `352210200026000319`
- **SKPDLB:** nomor dokumen `SKPDLB/2026/03/000001`; tidak ada kode billing penagihan baru
- **SKPDN:** nomor dokumen `SKPDN/2026/03/000001`; tidak ada kode billing penagihan baru

### 10.7 Ringkasan Dokumen

| Dokumen | Trigger Penerbitan | Penandatangan | Format |
|---------|-------------------|---------------|--------|
| Billing SA | Saat billing dibuat | — | PDF F4 |
| SPTPD | Saat billing `paid` dan `sptpd_number` diterbitkan setelah syarat `isTriwulanComplete()` terpenuhi | — | PDF F4 |
| STPD (Otomatis) | Saat billing `paid`, ada sanksi, bukan OPD/insidentil, dan triwulan terkait sudah lengkap; nomor dokumen dan kode pembayaran mengikuti billing asal | Pimpinan | PDF F4 |
| STPD (Manual) | Draft oleh petugas, terbit oleh verifikator; nomor dokumen memakai format STPD resmi, sedangkan kode pembayaran mengikuti billing asal untuk `pokok_sanksi` dan kode turunan `77` untuk `sanksi_saja`; PDF aktif setelah status disetujui | Pimpinan | PDF F4 |
| SKPD Reklame | Draft oleh petugas, terbit oleh verifikator; signed URL publik hanya untuk permohonan sewa yang disetujui | Pimpinan | PDF F4 |
| SKPD Air Tanah | Draft oleh petugas, terbit oleh verifikator | Pimpinan | PDF F4 |
| Surat Ketetapan Umum | Draft oleh admin/petugas, terbit oleh admin/verifikator; nomor dokumen memakai format surat, sedangkan billing penagihan baru hanya muncul pada `SKPDKB`/`SKPDKBT` | Pimpinan | PDF F4 |

### 10.8 Preview Dokumen Lokal

- Route `/document-previews` tersedia khusus pada environment `local` dan `testing`
- Akses dibatasi hanya untuk role `admin`
- Pintasan tersedia di navigasi backoffice Filament melalui halaman `Preview Dokumen` pada grup `Sistem`
- Seluruh preview menggunakan fixture in-memory untuk Billing, SPTPD, STPD, SKPD Reklame, SKPD Air Tanah, dan Surat Ketetapan
- Preview ini tidak menambahkan data ke tabel dokumen operasional seperti `taxes`, `stpd_manuals`, `skpd_reklame`, `skpd_air_tanah`, `tax_assessment_letters`, atau `wajib_pajak`

---

## 11. Sistem Billing & Pembayaran

### 11.1 Format Kode Billing

Kode billing terdiri dari **18 karakter** dengan pola umum: `35221XX[PADDING]YY[SEQUENCE]ZZ`

| Segmen | Panjang | Keterangan |
|--------|---------|------------|
| `35221` | 5 | Kode wilayah Bojonegoro |
| `XX` | 2 | 2 digit terakhir kode jenis pajak |
| Padding | Variabel | Zero-padding |
| `YY` | 2 | 2 digit tahun |
| Sequence | 4–6 digit | Nomor urut (auto-increment per jenis pajak per tahun) |
| `ZZ` | 2 | Suffix akhir billing; umumnya sama dengan `XX`, kecuali billing turunan `SKPDKB`/`SKPDKBT` yang memakai `19` |

**Tiga tier sequence:** ≤9999 (4 digit), ≤99999 (5 digit), ≤999999 (6 digit). Collision retry built-in.

**Catatan Surat Ketetapan:** billing turunan `SKPDKB` dan `SKPDKBT` tetap dihitung per jenis pajak dan tahun, tetapi dua digit terakhirnya tidak mengikuti suffix jenis pajak. Sistem memaksa digit ke-17 dan ke-18 menjadi `19`.

**Catatan STPD Manual `sanksi_saja`:** kode pembayaran STPD manual tidak membentuk billing `taxes` baru. Sistem menyimpan alias pembayaran pada `stpd_payment_code` yang diarahkan ke billing sumber yang sama, dengan pola perubahan digit ke-8 dan ke-9 dari `00` menjadi `77`.

### 11.2 Status Billing (Tax)

| Status | Label | Warna | Aktif? |
|--------|-------|-------|--------|
| `pending` | Menunggu Pembayaran | warning | ✅ |
| `paid` | Lunas | success | ✅ |
| `verified` | Terverifikasi | info | ✅ |
| `partially_paid` | Dibayar Sebagian | info | ✅ |
| `expired` | Kedaluwarsa | gray | ❌ |
| `rejected` | Ditolak | danger | ❌ |
| `cancelled` | Dibatalkan | gray | ❌ |

**Status aktif** (blocking duplikat): `pending`, `paid`, `verified`, `partially_paid`

### 11.3 Kanal Pembayaran

| Kode | Nama |
|------|------|
| `TOKOPEDIA` | Tokopedia |
| `ALFAMART` | Alfamart |
| `INDOMARET` | Indomaret |
| `QRISBJATIM` | QRIS Bank Jatim |
| `BJATIM` | Teller/Mobile Bank Jatim |
| `BNI` | Bank BNI |
| `MANUAL` | Transfer Langsung RKUD |

### 11.4 Pembayaran Parsial
- Sistem mendukung **pembayaran sebagian** (partially_paid)
- Record `TaxPayment` mencatat setiap pembayaran: jumlah, pokok, denda, channel, referensi
- Sisa tagihan dihitung: `max(0, (amount + sanksi) - totalPaid)`
- Status `partially_paid` sampai lunas penuh

### 11.5 Multi-Billing
Objek tertentu diizinkan memiliki beberapa billing aktif pada masa pajak yang sama:
- Objek **OPD** (instansi pemerintah / katering)
- Objek **Insidentil** (event-based)
- MBLB sub-jenis **WAPU** (pemungut wajib)

Untuk objek multi-billing tersebut, form billing memprefill masa pajak ke **bulan berjalan** dan `billing_sequence` dihitung per `nopd` pada periode yang sama.

### 11.6 Auto-Assignment Saat Lunas (TaxObserver)
Ketika status berubah ke `paid`:
- **SPTPD number** di-assign (= billing_code) jika triwulan lengkap
- **STPD number** di-assign jika ada sanksi > 0, triwulan lengkap, dan bukan OPD/insidentil
- Sibling billing dalam triwulan yang sama di-backfill

### 11.7 Deteksi Duplikat
Sebelum membuat billing, sistem otomatis memeriksa apakah sudah ada billing aktif untuk objek + masa pajak yang sama. Jika ada:
- Billing **lunas** → tawarkan pembuatan **pembetulan**
- Billing **pending** → tawarkan **pembatalan & penggantian**
- Objek multi-billing (OPD/insidentil/WAPU) → langsung lewati pengecekan

Untuk objek reguler, pemeriksaan histori billing aktif dan prefill masa pajak mengikuti `nopd` objek, sehingga jika ada beberapa record objek yang merepresentasikan `nopd` yang sama, sistem tetap membaca histori billing sebagai satu rangkaian yang sama.

---

## 12. Manajemen Data Master

### 12.1 Jenis Pajak
- **CRUD** jenis pajak: kode, nama, singkatan, deskripsi, ikon, tarif default, tipe assessment (self/official), opsen persen, urutan, status aktif
- **Akses backoffice:** Admin only
- Soft delete

### 12.2 Sub Jenis Pajak
- **CRUD** sub-jenis pajak: kode, nama, tarif persen, insidentil flag, dasar hukum, berlaku mulai/sampai
- **Akses backoffice:** Admin only
- Relasi ke jenis pajak induk
- Khusus reklame, sub jenis pajak dipakai sebagai kategori operasional/umbrella pada objek pajak dan flow SKPD, misalnya `REKLAME_TETAP` dan `REKLAME_KAIN`

### 12.3 Harga Patokan Reklame
- **CRUD** detail jenis reklame: kode detail, nama, sub jenis induk reklame, flag insidentil, urutan, status aktif
- **Akses backoffice:** Admin only
- Menjadi source of truth detail reklame `RKL_*` untuk kalkulator, tarif reklame, pembuatan SKPD, dan permohonan sewa reklame
- Masing-masing detail reklame memiliki child tariff temporal pada menu yang sama

### 12.4 Pimpinan (Penandatangan)
- **CRUD** data pimpinan: kabupaten, OPD, jabatan, bidang, sub-bidang, nama, pangkat, NIP
- **Akses backoffice:** Admin only
- Digunakan untuk tanda tangan digital pada SKPD dan STPD
- Verifikator di-assign ke satu Pimpinan

### 12.5 Harga Patokan MBLB
- **CRUD** harga patokan mineral: nama mineral, nama alternatif (JSON), harga patokan, satuan, dasar hukum, berlaku mulai/sampai
- **Akses backoffice:** Admin only
- Temporal — berlaku untuk periode tertentu

### 12.6 Harga Patokan Sarang Walet
- **CRUD** harga patokan sarang: nama jenis, harga, satuan, dasar hukum, berlaku mulai/sampai
- **Akses backoffice:** Admin only

### 12.7 Harga Satuan Listrik
- **CRUD** harga satuan listrik per wilayah: nama wilayah, harga per kWh, dasar hukum, berlaku mulai/sampai
- **Akses backoffice:** Admin only
- Digunakan untuk perhitungan PPJ Non-PLN

### 12.8 NPA Air Tanah
- **CRUD** NPA: kelompok pemakaian (1–5), kriteria SDA (1–4), NPA per m³, NPA tiers (bertingkat), berlaku mulai/sampai, dasar hukum
- **Akses backoffice:** Admin only
- Tier lookup: untuk setiap volume bracket, NPA yang berbeda bisa diterapkan

### 12.9 Tarif Pajak
- Tarif per sub-jenis pajak, temporal (berlaku mulai/sampai)
- Lookup otomatis berdasarkan sub_jenis_pajak_id + tanggal masa pajak
- Fallback chain: TarifPajak → objek.tarif_persen → jenisPajak.tarif_default → 10%

### 12.10 Wilayah
- **Kecamatan:** CRUD data kecamatan (admin only)
- **Desa/Kelurahan:** CRUD data desa (admin only)
- **Provinsi & Kabupaten:** Data referensi wilayah Indonesia

### 12.11 Kelompok Lokasi Jalan (Reklame)

- **CRUD** referensi kelompok lokasi dan nama jalan reklame
- **Akses backoffice:** Admin only
- Digunakan oleh kalkulator reklame, klasifikasi nilai strategis, dan penentuan tarif reklame tetap

| Kelompok | Kelas | Deskripsi |
|----------|-------|-----------|
| A | A | Jalan Utama/Protokol |
| A1 | A | Jalan Sekunder Utama |
| A2 | A | Jalan Sekunder |
| A3 | A | Jalan Lokal Utama |
| B | B | Jalan Lokal |
| C | C | Jalan Lingkungan |

---

## 13. Fitur Reklame (Pajak Iklan)

### 13.1 Objek Reklame
- Terdaftar di tabel `tax_objects` dengan scope jenis pajak `41104`
- Atribut: NIK, NPWPD, NOPD, nama reklame, alamat, kelurahan, kecamatan, kelompok lokasi, bentuk, dimensi (panjang/lebar/tinggi/dll), luas otomatis, jumlah muka, tarif, foto, GPS, tanggal pasang, masa berlaku
- Objek reklame menyimpan sub jenis pajak operasional reklame, bukan detail tarif `RKL_*`

### 13.2 Tarif Reklame
- Per detail harga patokan reklame + kelompok lokasi + satuan waktu
- Temporal (berlaku mulai/sampai)
- Komponen: NSPR, NJOPR, tarif pokok (= (NSPR+NJOPR) × 25%)
- Satuan waktu: perTahun, perBulan, perMinggu, perHari, perLembar, perMingguPerBuah, perHariPerBuah
- Source of truth detail reklame berada pada master **Harga Patokan Reklame**, sedangkan `Sub Jenis Pajak` reklame tetap dipakai sebagai kategori induk (`REKLAME_TETAP` atau `REKLAME_KAIN`)

### 13.3 Nilai Strategis Reklame
- Surcharge tambahan berdasarkan kelas kelompok (A/B/C) dan range luas
- Hanya untuk reklame tetap, luas ≥ 10m², satuan perTahun/perBulan
- Tarif per tahun dan per bulan terpisah

### 13.4 Perpanjangan Reklame
- WP dapat mengajukan perpanjangan via portal web atau mobile app
- Durasi: 30, 90, 180, atau 365 hari
- Syarat: izin reklame ≤ 30 hari dari kadaluarsa atau sudah kadaluarsa
- Status: diajukan → menunggu verifikasi → diproses → disetujui/ditolak

### 13.5 SKPD Reklame
- **Draft dibuat** dari flow operasional reklame, terutama oleh petugas saat memproses permohonan atau perpanjangan
- **Pemilihan jenis reklame:** flow SKPD menggunakan sub jenis reklame dari objek sebagai kategori induk, lalu petugas memilih detail reklame dari master Harga Patokan Reklame untuk lookup tarif
- **Verifikasi backoffice:** Halaman verifikasi SKPD Reklame diakses admin/verifikator untuk setujui & terbitkan atau tolak draft
- **Saat disetujui:** generate nomor SKPD, kode billing, record Tax, jatuh tempo, tanda tangan digital
- **Overlap protection:** Tidak bisa membuat SKPD dengan masa berlaku tumpang tindih pada objek yang sama
- Status: `draft` → `disetujui` / `ditolak`

**Dokumen PDF SKPD Reklame — Layout Tabel Perhitungan:**

| Mode | Objek WP (Reklame Umum) | Aset Pemkab (Sewa) |
|------|-------------------------|---------------------|
| Kolom 1 (label) | DPP (rowspan 8) + Pajak (rowspan 3) | Kosong (rowspan 10, merged) |
| Baris 1 | Dasar Pengenaan Pajak [(e×f)+(e×g)] | Spesifikasi Teknis (a–e: bentuk, panjang, lebar, perhitungan, luas) |
| Baris 2–6 | Sub-item a–e (bentuk s/d luas) + f. NSPR + g. NJOPR | Kategori Penyelenggaraan (Tahunan/Bulanan/Mingguan) |
| Baris 7 | Tarif Pajak 25% | Durasi Penyelenggaraan |
| Baris 8 | Nilai Strategis | Pajak Reklame (harga sewa per periode) |
| Baris 9 | Pokok Pajak (1×2)+3 | Pokok Pajak Terutang (3×4) |
| Nominal col | Grey merged untuk sub-item, nilai untuk DPP/tarif/pokok | Grey merged untuk sub-item spesifikasi, nilai untuk point 2–5 |

- Deteksi mode: `$isSewaPemkab = !empty($skpd->aset_reklame_pemkab_id)`
- Template: `resources/views/documents/skpd-reklame.blade.php`

### 13.6 Aset Reklame Pemkab (Milik Pemerintah)
- Pengelolaan aset reklame milik pemerintah (billboard, neon box)
- Atribut: kode aset, nama, jenis, lokasi, dimensi, harga sewa (per tahun/bulan/minggu), foto, GPS, kawasan, traffic
- **Akses backoffice:** Admin, verifikator, dan petugas dapat list/view; create/delete admin-only; update dan aksi operasional tersedia untuk admin/petugas
- **Aksi operasional:** `set maintenance`, `pinjam OPD`, `set tersedia`, dan `selesai pinjam` hanya tersedia untuk admin/petugas

**Status Ketersediaan:**

| Status | Deskripsi | Auto-Update |
|--------|-----------|-------------|
| `tersedia` | Belum disewa | ✅ |
| `disewa` | Sedang disewa | ✅ |
| `maintenance` | Sedang perbaikan | ❌ (manual) |
| `tidak_aktif` | Tidak aktif | ❌ (manual) |
| `dipinjam_opd` | Dipinjam OPD | ❌ (manual) |

**Observer:** Perubahan status SKPD yang terkait aset Pemkab memicu sinkronisasi status aset berdasarkan ada/tidaknya SKPD `disetujui` yang masih aktif; status manual `maintenance`, `tidak_aktif`, dan `dipinjam_opd` tidak ditimpa.

### 13.7 Peminjaman Aset Reklame (OPD)
- Fitur terpisah dari sewa komersial
- Untuk instansi pemerintah (OPD) meminjam aset reklame
- Data: OPD peminjam, materi pinjam, durasi, bukti dukung
- Status: `aktif` → `selesai`

### 13.8 Permohonan Sewa Reklame
- **Publik (tanpa login):** Ajukan sewa aset reklame Pemkab langsung dari website
- **Upload:** KTP dan desain reklame wajib; NPWP opsional
- **Akses backoffice:** List/detail permohonan hanya untuk petugas; verifikator bekerja pada tahap verifikasi SKPD yang dihasilkan, bukan pada resource permohonan
- **Aksi backoffice petugas:** `proses`, `tolak`, `cek NPWPD`, `buat NPWPD`, `perlu revisi`, dan `buat SKPD` sesuai status permohonan
- **Alur lengkap:**
  1. Pilih aset tersedia → isi form + upload dokumen → terima nomor tiket
  2. Petugas proses → cek/buat NPWPD → buat SKPD draft
  3. Verifikator setujui → SKPD terbit → dokumen dapat diakses via signed URL publik untuk SKPD permohonan sewa yang sudah `disetujui`
- **Revisi:** Jika status `perlu_revisi`, pemohon bisa memperbaiki dan submit ulang
- **Tracking:** Cek status kapan saja by nomor tiket

---

## 14. Fitur Air Tanah (Pajak Air Bawah Tanah)

### 14.1 Objek Pajak Air Tanah
- Terdaftar di tabel `tax_objects` dengan scope jenis pajak `41108`
- Jenis sumber air: Sumur Bor, Sumur Gali, Mata Air, Spring Well
- Atribut khusus: `uses_meter`, `last_meter_reading`, `last_report_date`, `kelompok_pemakaian` (1–5), `kriteria_sda` (1–4)

### 14.2 Laporan Meter (Meter Report)
- **Dari mobile app:** WP upload foto meter + lokasi GPS + angka meter sebelum/sesudah
- **Auto-calculate:** `usage = meter_after - meter_before`
- **GPS verification:** Sistem mencatat dan verifikasi lokasi
- **Status:** submitted → processing → approved / rejected
- **Akses backoffice:** Admin, verifikator, dan petugas dapat list/detail; proses laporan untuk membuat draft SKPD hanya dilakukan admin/petugas
- **Trigger:** Admin/petugas proses laporan → buat draft SKPD

### 14.3 NPA (Nilai Perolehan Air)
- Tarif per m³ berdasarkan kelompok pemakaian + kriteria SDA
- Sistem bertingkat (tiered) — tarif berbeda untuk bracket volume berbeda
- Temporal — berlaku mulai/sampai
- Fallback: jika tiers kosong tapi ada flat NPA/m³, gunakan flat rate

### 14.4 SKPD Air Tanah
- **Draft dibuat** dari:
  - Pemrosesan laporan meter (di halaman Laporan Meter)
  - Input manual (di halaman Buat SKPD Air Tanah)
- **Verifikasi backoffice:** Halaman verifikasi SKPD Air Tanah diakses admin/verifikator untuk setujui & terbitkan atau tolak draft
- **4 skenario pembuatan:**
  1. Objek baru (belum ada riwayat)
  2. Tanpa meter (input penggunaan langsung)
  3. Ganti meter (meter lama + meter baru)
  4. Normal (dengan riwayat meter)
- **Diverifikasi** oleh admin/verifikator
- **Saat disetujui:** generate nomor SKPD, kode billing, record Tax, jatuh tempo, update meter terakhir objek
- **Duplikasi guard:** Tidak bisa membuat SKPD untuk objek + periode yang sudah ada

### 14.5 Sumber Air dan Kelompok

**Kelompok Pemakaian:**
- Kelompok 1 s/d 5 (menentukan NPA rate)

**Kriteria SDA:**
| Kriteria | Deskripsi |
|----------|-----------|
| 1 | Air Tanah Kualitas Baik, Ada Sumber Alternatif |
| 2 | Air Tanah Kualitas Baik, Tidak Ada Sumber Alternatif |
| 3 | Air Tanah Kualitas Tidak Baik, Ada Sumber Alternatif |
| 4 | Air Tanah Kualitas Tidak Baik, Tidak Ada Sumber Alternatif |

---

## 15. Fitur Gebyar Sadar Pajak

### 15.1 Deskripsi
Program undian berhadiah untuk mendorong kesadaran pajak. Wajib pajak yang telah membayar pajak dapat mengirimkan bukti pembayaran untuk mendapatkan kupon undian.

### 15.2 Alur

```
[Wajib Pajak]                          [Admin/Petugas (Backoffice)]
     |                                        |
     |-- Submit bukti bayar pajak ----------->|
     |   (foto struk + jumlah transaksi)      |
     |                                        |
     |                                        |-- Verifikasi
     |                                        |   Sah → tambah kupon_count ke user
     |                                        |   Tolak → catatan alasan
     |                                        |
     |<----- Notifikasi hasil ----------------|
```

### 15.3 Detail
- **Input:** Jenis pajak, tanggal transaksi, jumlah transaksi, nama tempat usaha, foto bukti
- **Duplikasi:** Deteksi via `transaction_amount_hash` (SHA-256)
- **Periode:** Per tahun (`period_year`)
- **Output:** Kupon undian ditambahkan ke `user.total_kupon_undian`
- **Status:** pending → approved / rejected

---

## 16. Sistem Notifikasi

### 16.1 Dual Notification System

| Sistem | Tabel | Target | Digunakan Oleh |
|--------|-------|--------|----------------|
| App Notification | `app_notifications` | Wajib Pajak (portal & mobile) | NotificationService::notifyUser() / `notifyUserBoth()` |
| Filament Notification | `notifications` (Laravel) | Admin/Verifikator/Petugas (bell icon) | NotificationService::notifyRole() |

Catatan implementasi:
- `notifyUserBoth()` mengirim notifikasi ke `app_notifications` sekaligus ke database notification Filament untuk user yang relevan.

### 16.2 Event yang Memicu Notifikasi

| Event | Penerima | Tipe Notifikasi |
|-------|----------|-----------------|
| Billing dibuat | WP | App Notification |
| SKPD disetujui | WP | App Notification |
| SKPD ditolak | WP | App Notification |
| WP diverifikasi (setujui/tolak) | WP | App Notification |
| Pembetulan diproses | WP | App Notification |
| Pembetulan selesai / billing pengganti dibuat | WP | App Notification |
| Pembetulan ditolak | WP | App Notification |
| Perubahan data disetujui | WP | App Notification |
| Perubahan data ditolak | WP | App Notification |
| Laporan meter diproses | WP | App Notification |
| Draft SKPD Air Tanah baru menunggu verifikasi | Verifikator | Filament Notification |
| Gebyar diverifikasi | WP | App Notification |
| Permohonan sewa diproses | WP | App Notification |
| Data change request diproses | WP | App Notification |
| SKPD draft dibuat | Verifikator | Filament Notification |
| Meter report submitted | Petugas | Filament Notification |
| Pembayaran manual | WP | App Notification |

### 16.3 Broadcast
- `Notification::broadcast()` — kirim notifikasi ke semua user dengan role `wajibPajak`

---

## 17. Keamanan & Enkripsi Data

### 17.1 Enkripsi Data (HasEncryptedAttributes)
Semua data PII (Personally Identifiable Information) dienkripsi pada level kolom database:

| Kategori | Field yang Dienkripsi |
|----------|-----------------------|
| Identitas | `nik`, `nama_lengkap`, `tempat_lahir`, `tanggal_lahir`, `alamat` |
| Kontak | `no_whatsapp`, `no_telp`, `email` (pada beberapa model) |
| Keuangan | `amount`, `omzet`, `sanksi`, `opsen`, `harga_patokan`, `harga_per_kwh`, `jumlah_pajak`, `dasar_pengenaan` |
| Dokumen | `foto_ktp_url`, `foto_selfie_url`, `ttd_elektronik_url`, `qr_code_url` |

**Mekanisme:**
- Enkripsi otomatis saat `saving` event
- Dekripsi transparan saat `getAttribute()` (read)
- Deteksi double-encryption untuk mencegah enkripsi ganda
- Hash column (SHA-256) dibuat dari nilai plaintext sebelum enkripsi untuk field yang memerlukan pencarian (contoh: `nik_hash`, `email_hash`)

**Konsekuensi:**
- SQL aggregation tidak bisa digunakan pada field enkripsi
- Pencarian dilakukan melalui hash column atau in-memory filtering
- `TotalPaid` pada Tax dihitung melalui decrypt per-record di PHP

### 17.2 Autentikasi & Keamanan Login
- **Password:** Hashed (Laravel default bcrypt/argon2)
- **PIN:** 6 digit, hashed, untuk operasi sensitif di mobile app
- **Account Lockout (portal web):** 5 kali gagal login → dikunci 15 menit
- **State lockout:** disimpan pada `failed_login_attempts` dan `locked_until` di model `User`
- **OTP:**
  - 6 digit, expired dalam 30 detik
  - Maksimal 3 percobaan per OTP
  - Cooldown 2 menit antar permintaan
  - Rate limit: maksimal 3 resend per 15 menit
  - Verification token (64 karakter) berlaku 15 menit setelah verifikasi OTP

### 17.3 API Security
- **Sanctum tokens** untuk mobile app
- **Session-based auth** untuk web portal dan admin panel Filament
- **Signed URLs** untuk akses publik dokumen SKPD permohonan sewa reklame yang sudah disetujui
- **CSRF protection** pada semua form web

### 17.4 Soft Delete
**Seluruh** model yang berhubungan dengan data CRUD menggunakan soft delete (data tidak dihapus permanen dari database):

| Kategori | Model |
|----------|-------|
| Auth | `User` |
| Master | `JenisPajak`, `SubJenisPajak`, `Pimpinan` |
| Wajib Pajak | `WajibPajak` |
| Pajak | `Tax`, `TaxPayment`, `TaxObject`, `TaxMblbDetail`, `TaxSarangWaletDetail`, `TaxPpjDetail`, `TarifPajak` |
| Harga Patokan | `HargaPatokanMblb`, `HargaPatokanSarangWalet`, `HargaSatuanListrik`, `HargaPatokanReklame` |
| Reklame | `ReklameRequest`, `SkpdReklame`, `AsetReklamePemkab`, `PermohonanSewaReklame`, `PeminjamanAsetReklame`, `KelompokLokasiJalan`, `ReklameTariff`, `ReklameNilaiStrategis`, `ReklameObject` |
| Air Tanah | `WaterObject`, `MeterReport`, `SkpdAirTanah`, `NpaAirTanah` |
| Surat Ketetapan | `TaxAssessmentLetter`, `TaxAssessmentCompensation` |
| STPD | `StpdManual` |
| Pembetulan | `PembetulanRequest` |
| CMS | `News`, `Destination` |
| Gebyar | `GebyarSubmission` |
| MBLB Portal | `PortalMblbSubmission` |
| Wilayah | `District`, `Village` |
| Utilitas | `Notification`, `AppVersion`, `DataChangeRequest` |

**Pengecualian** (tanpa soft delete):
- `VerificationCode` — token sementara, dibersihkan otomatis
- `ActivityLog` — audit trail, tidak boleh dihapus
- `Province`, `Regency` — data referensi statis (seed data)
- `DatObjekPajak`, `DatSptpdAt` — data read-only dari Simpadu

Fitur di Filament admin panel:
- TrashedFilter untuk melihat data yang sudah dihapus
- RestoreBulkAction & ForceDeleteBulkAction untuk restore/hapus permanen
- RestoreAction & ForceDeleteAction di halaman edit
- Kode billing dan SKPD number cek uniqueness termasuk data yang soft-deleted (`withTrashed()`)

---

## 18. Integrasi Sistem Lama (Simpadu)

### 18.1 Deskripsi
Koneksi **read-only** ke database `simpadu` (sistem lama) untuk referensi data historis air tanah. Model legacy yang ada saat ini tidak menulis balik ke database lama.

### 18.2 Model

| Model | Tabel | Deskripsi |
|-------|-------|-----------|
| `DatObjekPajak` | `dat_objek_pajak` | Data objek pajak legacy, PK: NOP (string) |
| `DatSptpdAt` | `dat_sptpd_at` | SPTPD Air Tanah legacy |

### 18.3 Data yang Diambil
- `DatObjekPajak`: NOP, NAME, JALAN_OP, STATUS, computed: meter_terakhir, tanggal_lapor_terakhir, masa_pajak_terakhir
- `DatSptpdAt`: nop, hariini (meter reading), masa_awal (period), tgldata

### 18.4 Kegunaan
- Referensi riwayat meter air tanah dari sistem lama melalui relasi `DatObjekPajak -> sptpdAt`
- Membaca meter terakhir, tanggal lapor terakhir, dan masa pajak terakhir dari data legacy saat dibutuhkan
- Migrasi data legacy ke sistem baru didukung terpisah; hasil migrasi ditandai dengan flag `is_legacy` dan `legacy_billing_code` pada model pajak/dokumen terkait

---

## 19. Fitur CMS (Content Management)

### 19.1 Berita (News)
- **CRUD** berita melalui backoffice Filament
- **Atribut utama:** judul, slug (auto-generate dan dijaga unik), excerpt, content, image_url, published_at, kategori, author, view_count, featured badge, source_url
- **Upload gambar backoffice:** file gambar diubah ke WebP, di-resize ke 1200×675, lalu dikompresi hingga maksimal sekitar 1 MB
- **Kategori:** pengumuman, pajak, event, edukasi, lainnya
- **Scope/model helper:** `published` (`published_at <= now()`), `featured`, `category(...)`, `latestPublished()`, `incrementViewCount()`
- **Publik web:** `/berita` menampilkan berita published dengan filter kategori; `/berita/{slug}` menaikkan `view_count` dan memuat 3 berita terkait terbaru
- **Publik API:** `GET /api/v1/news` mengembalikan berita published dalam bentuk paginasi

### 19.2 Destinasi (Wisata/Kuliner)
- **CRUD** destinasi melalui backoffice Filament
- **Atribut utama:** name, slug (auto-generate dan dijaga unik), description, address, category, image_url, rating, review_count, price_range, facilities (array/JSON), phone (encrypted), website, latitude, longitude, featured badge
- **Upload gambar backoffice:** file gambar diubah ke WebP, di-resize ke 1200×675, lalu dikompresi hingga maksimal sekitar 1 MB
- **Kategori:** wisata, kuliner, hotel, oleh-oleh, hiburan
- **Scope/model helper:** `category(...)`, `featured`, accessor `category_label`
- **Publik web:** `/destinasi` dan `/destinasi/{slug}` memakai route key `slug`
- **Publik API:** `GET /api/v1/destinations` mendukung filter `category` dan `featured`; field `phone` disembunyikan dari respons API

---

## 20. Sistem Sanksi & Denda

### 20.1 Aturan Jatuh Tempo

| Jenis Pajak | Periode | Aturan Jatuh Tempo |
|-------------|---------|-------------------|
| Self-Assessment (Hotel, Restoran, Parkir, Hiburan) | Sebelum 2024 | Akhir bulan berikutnya |
| Self-Assessment | Jan 2024 – Jun 2025 | Hari kerja ke-10 bulan berikutnya |
| Self-Assessment | ≥ Jul 2025 | Hari kerja ke-10 bulan pertama triwulan berikutnya |
| Reklame | Semua | masa_berlaku_mulai + 1 bulan - 1 hari |
| Air Tanah | Semua | Akhir bulan berikutnya dari periode |
| Sarang Walet (portal) | — | 7 hari dari tanggal billing |
| Sarang Walet (petugas) | — | 1 bulan dari tanggal billing |

### 20.2 Perhitungan Hari Kerja
- Melewatkan akhir pekan (Sabtu, Minggu)
- Melewatkan hari libur nasional dan cuti bersama Pemerintah Indonesia yang disimpan statis di kode untuk periode 2024–2026
- Metode: `getNthWorkingDay(startMonth, n)` → hari kerja ke-n

### 20.3 Tarif Sanksi

| Periode Masa Pajak | Tarif per Bulan |
|---------------------|-----------------|
| Sebelum Januari 2024 | 2% |
| Januari 2024 ke atas | 1% |

### 20.4 Perhitungan Denda

```
Untuk setiap bulan keterlambatan (max 24 bulan):
  Jika bulan < Jan 2024 → denda += pokok_pajak × 2%
  Jika bulan ≥ Jan 2024 → denda += pokok_pajak × 1%

Total Tagihan = Pokok Pajak + Total Denda
```

**Catatan:**
- Maksimal **24 bulan** keterlambatan
- **Dynamic split-rate:** bulan yang jatuh sebelum/sesudah Jan 2024 menggunakan tarif berbeda
- Nilai `tarif_sanksi` yang dikembalikan helper tetap memakai tarif awal masa pajak untuk kompatibilitas, walaupun akumulasi `denda` dihitung per bulan kalender keterlambatan
- **Pengecualian bebas denda:**
  - Objek **OPD** (instansi pemerintah) → denda selalu 0
  - Objek **Insidentil** (event-based) → denda selalu 0

### 20.5 Ketetapan Pasal 130

Untuk modul surat ketetapan daerah, sistem menggunakan rezim perhitungan tersendiri dan tidak mengikuti helper sanksi billing reguler.

| Jenis | Dasar | Komponen |
|-------|-------|----------|
| `SKPDKB` | Hasil pemeriksaan | Bunga `1,8%` per bulan dari pokok kurang bayar |
| `SKPDKB` | Tidak sampaikan SPTPD / tidak kooperatif | Bunga `2,2%` per bulan + kenaikan satu kali |
| `SKPDKB` PBJT | Secara jabatan | Kenaikan `50%` dari pokok kurang bayar |
| `SKPDKB` Non-PBJT | Secara jabatan | Kenaikan `25%` dari pokok kurang bayar |
| `SKPDKBT` | Data baru / tambahan | Kenaikan `100%` satu kali dari pokok tambahan |
| `SKPDLB` | Lebih bayar | Menjadi saldo kredit yang dapat dikompensasikan |
| `SKPDN` | Nihil | Tidak membentuk bunga, kenaikan, atau billing baru |

Catatan implementasi:
- Kenaikan sanksi hanya diterapkan satu kali saat dokumen diterbitkan.
- `PBJT` dalam konteks modul ini mencakup kode pajak `41101`, `41102`, `41103`, dan `41107`.

---

## 21. Sistem Audit Trail

### 21.1 Activity Log
- **Model:** `ActivityLog` — read-only di backoffice
- **Data dicatat:** actor_id, actor_type, action, target_table, target_id, description, old_values (encrypted with plaintext fallback on read), new_values (encrypted with plaintext fallback on read), ip_address, user_agent
- **Otomatis:** `logChanges()` mendeteksi perubahan atribut dan menyimpan old vs new values
- **Filter:** By actor, action, date range
- **Akses baca:** admin, verifikator, petugas
- **Mutasi data:** create/update manual ditolak oleh policy; log bersifat immutable dan dihasilkan oleh sistem
- **Aksi destruktif:** delete/restore/force delete dibatasi ke admin oleh policy

### 21.2 Data Change Request
- **Workflow formal** untuk perubahan data sensitif pada entity yang saat ini didukung: `wajib_pajak` dan `tax_objects`
- **Field changes:** Disimpan sebagai JSON terenkripsi (`{field: {old, new}}`)
- **Status:** pending → approved / rejected
- **Pengajuan:** Dibuat otomatis dari flow edit backoffice untuk field identitas/sensitif oleh admin/petugas
- **Reviewer:** Direview dari modul Verifikasi oleh admin/verifikator
- **Approval:** Menerapkan perubahan langsung ke entity asal + log audit
- **Rejection:** Menyimpan catatan review tanpa mengubah entity asal
- **Tampilan:** Tabel perbandingan old → new values di halaman detail

---

## 22. Versi Aplikasi Mobile

### 22.1 App Version Management
- **Model:** `AppVersion` — per platform (`android` / `ios`)
- **Akses backoffice:** admin-only sesuai `AppVersionPolicy`
- **Atribut:** platform, min_version, latest_version, force_update, maintenance_mode, message, store_url

### 22.2 Fitur
- **Helper model:** `getAndroid()`, `getIos()`, `needsUpdate(currentVersion)`, `isUnderMaintenance()`
- **Version check:** perbandingan versi memakai `version_compare(currentVersion, min_version, '<')`
- **Force update:** `force_update` flag menandai update wajib pada platform terkait
- **Maintenance mode:** `maintenance_mode` flag menandai aplikasi sedang nonaktif sementara dengan custom message
- **Store URL:** Link ke Google Play / App Store untuk update
- **Catatan implementasi saat ini:** model dan policy sudah tersedia, tetapi route API/web khusus untuk version check belum terdokumentasi di route set aktif saat ini

---

## Lampiran

### A. Daftar Model Utama

| Domain | Model | Tabel DB | Keterangan |
|--------|-------|----------|------------|
| Auth | User | users | Pengguna sistem |
| Auth | VerificationCode | verification_codes | OTP registrasi |
| WajibPajak | WajibPajak | wajib_pajaks | Data wajib pajak |
| Tax | Tax | taxes | Transaksi pajak / billing |
| Tax | TaxObject | tax_objects | Objek pajak (shared) |
| Tax | TaxPayment | tax_payments | Record pembayaran |
| Tax | TaxMblbDetail | tax_mblb_details | Detail mineral MBLB per billing |
| Tax | TaxSarangWaletDetail | tax_sarang_walet_details | Detail sarang walet per billing |
| Tax | TaxPpjDetail | tax_ppj_details | Detail PPJ per billing |
| Tax | PembetulanRequest | pembetulan_requests | Permintaan koreksi billing |
| Tax | PortalMblbSubmission | portal_mblb_submissions | Pengajuan billing MBLB portal sebelum verifikasi |
| Tax | StpdManual | stpd_manuals | Draft dan verifikasi STPD manual |
| Tax | TarifPajak | tarif_pajaks | Tarif pajak (temporal) |
| Tax | HargaPatokanMblb | harga_patokan_mblbs | Harga patokan mineral |
| Tax | HargaPatokanSarangWalet | harga_patokan_sarang_walets | Harga patokan sarang walet |
| Tax | HargaSatuanListrik | harga_satuan_listriks | Harga satuan listrik |
| Reklame | ReklameObject | tax_objects (scoped) | Objek pajak reklame |
| Reklame | ReklameRequest | reklame_requests | Permohonan perpanjangan |
| Reklame | SkpdReklame | skpd_reklame | SKPD reklame |
| Reklame | ReklameTariff | reklame_tariffs | Tarif reklame |
| Reklame | ReklameNilaiStrategis | reklame_nilai_strategis | Nilai strategis reklame |
| Reklame | KelompokLokasiJalan | kelompok_lokasi_jalan | Kelompok lokasi jalan |
| Reklame | AsetReklamePemkab | aset_reklame_pemkab | Aset reklame pemerintah |
| Reklame | PermohonanSewaReklame | permohonan_sewa_reklame | Permohonan sewa reklame |
| Reklame | PeminjamanAsetReklame | peminjaman_aset_reklame | Peminjaman aset OPD |
| AirTanah | WaterObject | tax_objects (scoped) | Objek air tanah |
| AirTanah | MeterReport | meter_reports | Laporan meter |
| AirTanah | SkpdAirTanah | skpd_air_tanah | SKPD air tanah |
| AirTanah | NpaAirTanah | npa_air_tanah | NPA referensi |
| Master | JenisPajak | jenis_pajaks | Jenis pajak |
| Master | SubJenisPajak | sub_jenis_pajaks | Sub jenis pajak |
| Master | Pimpinan | pimpinans | Data pimpinan |
| Region | Province | provinces | Provinsi |
| Region | Regency | regencies | Kabupaten/Kota |
| Region | District | districts | Kecamatan |
| Region | Village | villages | Desa/Kelurahan |
| CMS | News | news | Berita |
| CMS | Destination | destinations | Destinasi |
| Gebyar | GebyarSubmission | gebyar_submissions | Submission undian |
| Simpadu | DatObjekPajak | dat_objek_pajak | Legacy objek pajak |
| Simpadu | DatSptpdAt | dat_sptpd_at | Legacy SPTPD AT |
| Shared | ActivityLog | activity_logs | Audit trail |
| Shared | AppVersion | app_versions | Versi mobile app |
| Shared | Notification | app_notifications | Notifikasi in-app |
| Shared | Team | teams | Tim |
| Shared | DataChangeRequest | data_change_requests | Permintaan perubahan data |

### B. Daftar Endpoint API Lengkap

**Publik (tanpa autentikasi):**
- `POST /api/v1/auth/request-otp` — Request OTP
- `POST /api/v1/auth/verify-otp` — Verifikasi OTP
- `POST /api/v1/auth/resend-otp` — Resend OTP
- `POST /api/v1/register` — Registrasi
- `POST /api/v1/login` — Login
- `GET /api/v1/master/provinces` — Daftar provinsi
- `GET /api/v1/master/regencies` — Daftar kabupaten/kota
- `GET /api/v1/master/districts` — Daftar kecamatan
- `GET /api/v1/master/villages/{district}` — Daftar desa
- `GET /api/v1/master/tax-types` — Jenis pajak aktif
- `GET /api/v1/news` — Berita
- `GET /api/v1/destinations` — Destinasi
- `GET /api/v1/billing/check` — Cek billing
- `GET /api/cek-npwpd/{npwpd}` — Cek NPWPD

**Autentikasi diperlukan (Sanctum):**
- `POST /api/v1/logout` — Logout
- `GET /api/v1/profile` — Profil user
- `POST /api/v1/update-profile` — Update profil
- `POST /api/v1/update-password` — Ubah password
- `POST /api/v1/update-pin` — Set/update PIN
- `POST /api/v1/verify-pin` — Verifikasi PIN
- `GET /api/v1/water-objects` — Daftar objek air tanah
- `POST /api/v1/water-objects` — Daftarkan objek air tanah
- `POST /api/v1/water-reports` — Kirim laporan meter
- `GET /api/v1/water-reports/history` — Riwayat laporan
- `GET /api/v1/reklame-objects` — Daftar objek reklame
- `POST /api/v1/reklame-extensions` — Ajukan perpanjangan reklame
- `GET /api/v1/reklame-requests` — Riwayat permohonan reklame
- `GET /api/v1/reklame-aset-pemkab` — Daftar aset reklame Pemkab
- `POST /api/v1/reklame-sewa` — Ajukan sewa reklame
- `GET /api/v1/reklame-sewa` — Riwayat permohonan sewa
- `POST /api/v1/taxes/self-assessment` — Buat billing self-assessment
- `GET /api/v1/taxes/history` — Riwayat transaksi
- `POST /api/v1/gebyar/submit` — Submit Gebyar
- `GET /api/v1/gebyar/history` — Riwayat Gebyar
- `GET /api/v1/notifications` — Daftar notifikasi
- `GET /api/v1/notifications/unread-count` — Jumlah belum dibaca
- `POST /api/v1/notifications/{id}/read` — Tandai dibaca
- `POST /api/v1/notifications/read-all` — Tandai semua dibaca

### C. Daftar Halaman Web Portal

**Publik:**
- `/` — Landing page
- `/login` — Login
- `/cek-billing` — Cek billing
- `/produk-hukum` — Produk hukum
- `/kalkulator-sanksi` — Kalkulator sanksi
- `/kalkulator-air-tanah` — Kalkulator air tanah
- `/kalkulator-reklame` — Kalkulator reklame
- `/sewa-reklame` — Sewa reklame (info + peta)
- `/sewa-reklame/ajukan/{asetId}` — Form sewa reklame
- `/sewa-reklame/cek` — Cek tiket sewa
- `/sewa-reklame/detail/{nomorTiket}` — Detail permohonan
- `/sewa-reklame/edit/{nomorTiket}` — Revisi permohonan
- `/berita` — Daftar berita
- `/berita/{slug}` — Detail berita
- `/destinasi` — Daftar destinasi
- `/destinasi/{slug}` — Detail destinasi

**Portal WP (login diperlukan):**
- `/portal/dashboard` — Dashboard
- `/portal/self-assessment` — Self-assessment wizard
- `/portal/self-assessment/{jenisPajakId}/create` — Form self-assessment untuk jenis pajak terpilih
- `/portal/self-assessment/{taxId}/success` — Halaman sukses setelah billing dibuat
- `/portal/pembetulan/{taxId}` — Pembetulan billing
- `/portal/riwayat` — Riwayat transaksi
- `/portal/cek-billing` — Cek billing (portal view)
- `/portal/air-tanah` — Hub air tanah
- `/portal/air-tanah/objek` — Objek air tanah
- `/portal/air-tanah/skpd` — SKPD air tanah
- `/portal/air-tanah/skpd/{skpdId}` — Detail SKPD
- `/portal/reklame` — Hub reklame
- `/portal/reklame/objek` — Objek reklame
- `/portal/reklame/objek/{objectId}` — Detail objek
- `/portal/reklame/objek/{objectId}/perpanjangan` — Perpanjangan reklame
- `/portal/reklame/skpd` — SKPD reklame
- `/portal/reklame/skpd/{skpdId}` — Detail SKPD
- `/portal/billing/{taxId}/document` — Lihat billing
- `/portal/billing/{taxId}/download` — Unduh billing
- `/portal/billing/{taxId}/sptpd` — Unduh SPTPD
- `/portal/billing/{taxId}/sptpd/view` — Lihat SPTPD
- `/portal/billing/{taxId}/stpd` — Unduh STPD
- `/portal/billing/{taxId}/stpd/view` — Lihat STPD
- `/portal/billing/{taxId}/status` — Cek status billing
- `/portal/notifications` — Notifikasi
- `/portal/notifications/unread-count` — Jumlah notifikasi belum dibaca
- `/portal/notifications/{id}/read` — Tandai notifikasi dibaca
- `/portal/notifications/read-all` — Tandai semua notifikasi dibaca

**Dokumen autentikasi/backoffice (di luar prefix `/portal`):**
- `POST /logout` — Logout portal/backoffice
- `/skpd-reklame/{skpdId}/view` — Lihat PDF SKPD Reklame
- `/skpd-reklame/{skpdId}/download` — Unduh PDF SKPD Reklame
- `/skpd-air-tanah/{skpdId}/view` — Lihat PDF SKPD Air Tanah
- `/skpd-air-tanah/{skpdId}/download` — Unduh PDF SKPD Air Tanah
- `/stpd-manual/{stpdId}/view` — Lihat STPD Manual (PDF stream)
- `/stpd-manual/{stpdId}/download` — Unduh STPD Manual (PDF download)
- `/permohonan-sewa/{id}/file/{field}` — Lihat lampiran permohonan sewa reklame (KTP/NPWP/desain), hanya untuk pemilik permohonan atau role backoffice; field di luar whitelist mengembalikan `404`
- `/admin/toggle-navigation` — Toggle mode navigasi backoffice pengguna login
