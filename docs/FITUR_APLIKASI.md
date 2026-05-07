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
14a. [Fitur Retribusi Sewa Tanah](#14a-fitur-retribusi-sewa-tanah)
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

- **Timezone default aplikasi:** `Asia/Jakarta` (WIB) untuk proses `now()`, format tanggal/jam di portal, backoffice, notifikasi sesi, dan dokumen PDF, kecuali ada konversi eksplisit lain pada kode.

### Tiga Antarmuka Utama

1. **Backoffice (Admin Panel)** — `/admin` — Filament-based, untuk pengelola (admin, verifikator, petugas)
2. **Portal Wajib Pajak (Web)** — `/portal/*` — untuk wajib pajak melalui browser
3. **Aplikasi Mobile (API)** — `/api/v1/*` — untuk wajib pajak melalui aplikasi mobile

- **Pemisahan sesi browser web:** backoffice dan portal memakai guard sesi browser yang berbeda, sehingga satu browser yang sama dapat menahan satu sesi backoffice dan satu sesi portal secara bersamaan pada tab berbeda. Seluruh role backoffice (`admin`, `verifikator`, `petugas`) tetap berbagi guard/sesi backoffice yang sama, sehingga login backoffice kedua pada browser yang sama akan menggantikan login backoffice sebelumnya.

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
| Data Master (Jenis Pajak, Pimpinan, Instansi, dll) | ✅ | ❌ | ❌ |
| Harga Patokan (MBLB, Walet, Listrik, Reklame) | ✅ | ❌ | ❌ |
| Nilai Strategis Reklame | ✅ | ❌ | ❌ |
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
| Pengajuan Reklame Portal | ✅ | ❌ | ✅ |
| Aset Reklame Pemkab | ✅ (list/view/update + aksi operasional) | ✅ (list/view + maintenance/pinjam OPD) | ✅ (list/view + maintenance/pinjam OPD) |
| Permintaan Pembetulan | ✅ | ❌ | ✅ |
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
| `42101` | Retribusi Sewa Tanah | Sewa Tanah | Official Assessment | Nominal tetap | — |

> **Catatan Billing Retribusi:** Kode jenis pajak `42101` mempunyai `billing_kode_override` ke `41104` sehingga kode billing yang dihasilkan menggunakan prefix `3522104` (sama dengan Reklame), menjaga kompatibilitas dengan sistem billing lama.

### Sub Jenis Pajak Penting

| Kode Sub | Jenis Induk | Nama | Keterangan |
|----------|-------------|------|------------|
| `PPJ_SUMBER_LAIN` | PPJ (41105) | PPJ Sumber Lain (PLN) | Pokok pajak diinput langsung |
| `PPJ_DIHASILKAN_SENDIRI` | PPJ (41105) | PPJ Dihasilkan Sendiri | Formula NJTL berbasis kapasitas kVA |
| `MBLB_WAPU` | MBLB (41106) | MBLB Pemungut | Multi-billing per masa pajak |
| *(insidentil)* | Hiburan/Parkir | Event-based | Bebas denda, multi-billing |
| *(Katering/OPD)* | Restoran | OPD/Katering | Bebas denda, multi-billing |
| `SEWA_TANAH_PERMANEN` | Sewa Tanah (42101) | Pemakaian Tanah untuk Pemasangan Reklame Permanen | Rate final Rp 80.000/tahun |
| `SEWA_TANAH_KAIN` | Sewa Tanah (42101) | Pemakaian Tanah untuk Pemasangan Kain Reklame/Umbul-umbul | Rate final Rp 20.000/bulan, dipakai untuk reklame insidentil |
| `SEWA_TANAH_RUMIJA` | Sewa Tanah (42101) | Pemakaian Tanah untuk Ruang Udara diatas RUMIJA | Rate final Rp 80.000/tahun |

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
- **Master instansi terkait:** admin dapat mengelola daftar OPD/instansi/lembaga beserta kategori dan alamat/lokasi dari menu Master Data
- **Portal wajib pajak:** pengajuan MBLB tidak langsung menerbitkan billing code; data mineral dan lampiran masuk ke antrean verifikasi admin/verifikator terlebih dahulu
- **Lampiran portal MBLB:** wajib gambar atau PDF; PDF maksimal 1 MB, gambar otomatis dikompres ke <= 1 MB saat disimpan
- **Akses lampiran portal MBLB:** lampiran pada detail atau form revisi pengajuan, termasuk preview detail verifikator di admin, dibuka melalui route aplikasi yang tervalidasi berdasarkan role/kepemilikan, sehingga tidak bergantung pada URL `/storage` publik server
- **Instansi opsional:** billing MBLB `MBLB_WAPU` di backoffice maupun portal dapat menyimpan instansi terkait sebagai snapshot histori (`instansi_id`, nama, kategori)
- **Pencarian portal MBLB:** instansi/lembaga memakai searchable combobox tunggal, sedangkan jenis material memakai filter langsung yang otomatis menyaring daftar material saat diketik
- **Menu portal Pengajuan MBLB:** wajib pajak memiliki halaman khusus untuk melihat submission MBLB portal berdasarkan status `menunggu verifikasi`, `disetujui`, atau `ditolak`; halaman detail menampilkan catatan verifikator, dan submission yang ditolak dapat diperbaiki lalu dikirim ulang
- **Riwayat status pengajuan MBLB:** setiap pengajuan baru, penolakan, persetujuan, dan pengiriman ulang setelah perbaikan dicatat sebagai histori status yang dapat dilihat pada detail pengajuan portal maupun detail verifikator di admin
- **Riwayat verifikasi lintas modul:** perubahan status baru pada Pembetulan, Perubahan Data, Pengajuan Reklame Portal, SKPD Air Tanah, STPD Manual, dan Gebyar juga dicatat sebagai histori verifikasi; histori ditampilkan di surface admin yang sudah ada, serta di portal untuk modul yang memang sudah memiliki halaman detail pemilik
- **Portal owner-facing histori verifikasi:** wajib pajak kini memiliki halaman atau detail khusus untuk memantau histori verifikasi Pembetulan, Perubahan Data, STPD Manual, Gebyar, selain detail portal yang sudah ada untuk MBLB, Reklame, dan Air Tanah

#### Panduan Melihat Riwayat Verifikasi

Bagian ini dibuat untuk memudahkan wajib pajak maupun petugas saat ingin mengecek posisi proses verifikasi terbaru pada setiap modul.

**Portal**

1. **Pengajuan MBLB**: buka menu `Layanan Pajak > Pengajuan MBLB`, lalu pilih salah satu pengajuan untuk melihat detail dan bagian **Riwayat Verifikasi**.
2. **Pembetulan**: buka `Layanan Pajak > Ajukan Pembetulan`, lalu buka detail permohonan pada daftar riwayat pembetulan.
3. **Perubahan Data**: buka `Layanan Pajak > Perubahan Data`, lalu pilih salah satu permintaan untuk melihat detail dan riwayat verifikasinya.
4. **Air Tanah**: buka `Layanan Pajak > Air Tanah`, masuk ke daftar SKPD Air Tanah, lalu buka detail SKPD.
5. **Reklame**: buka `Layanan Pajak > Reklame`, lalu buka detail objek reklame. Riwayat verifikasi pengajuan atau perpanjangan tampil pada halaman detail objek.
6. **STPD Manual**: buka `Layanan Pajak > STPD Manual`, lalu pilih salah satu STPD untuk melihat detail dan riwayat verifikasinya.
7. **Gebyar Pajak**: buka `Layanan Pajak > Gebyar Pajak`, lalu pilih salah satu pengajuan untuk melihat detail dan riwayat verifikasinya.

**Admin**

1. **Portal MBLB Submission**: buka resource Portal MBLB Submission, lalu klik aksi **Detail** pada baris data.
2. **Permintaan Pembetulan**: buka resource Permintaan Pembetulan, lalu klik aksi **Detail**.
3. **Perubahan Data**: buka resource Perubahan Data, lalu buka halaman view record. Bagian **Riwayat Verifikasi** tampil di halaman view.
4. **SKPD Air Tanah**: buka resource SKPD Air Tanah, lalu buka halaman view record. Bagian **Riwayat Verifikasi** tampil di halaman view.
5. **Reklame Request**: buka resource Reklame Request, lalu klik aksi **Detail**.
6. **STPD Manual**: buka resource STPD Manual, lalu klik aksi **Detail**.
7. **Gebyar Sadar Pajak**: buka resource Gebyar Sadar Pajak, lalu klik aksi **Detail**.

**URL Penting Halaman Verifikasi**

- Portal MBLB: `/portal/pengajuan-mblb`, lalu detail `/portal/pengajuan-mblb/{submissionId}`
- Portal Pembetulan: `/portal/pembetulan`, lalu detail `/portal/pembetulan/permohonan/{requestId}`
- Portal Perubahan Data: `/portal/perubahan-data`, lalu detail `/portal/perubahan-data/{requestId}`
- Portal Air Tanah: `/portal/air-tanah/skpd`, lalu detail `/portal/air-tanah/skpd/{skpdId}`
- Portal Reklame: `/portal/reklame/objek`, lalu detail `/portal/reklame/objek/{objectId}`
- Portal STPD Manual: `/portal/stpd-manual`, lalu detail `/portal/stpd-manual/{stpdId}`
- Portal Gebyar Pajak: `/portal/gebyar`, lalu detail `/portal/gebyar/{submissionId}`
- Admin Perubahan Data: `/admin/data-change-requests/{record}`
- Admin SKPD Air Tanah: `/admin/skpd-air-tanahs/{record}`
- Beberapa halaman admin lain seperti Portal MBLB Submission, Permintaan Pembetulan, Reklame Request, STPD Manual, dan Gebyar memakai aksi **Detail** di tabel list, sehingga histori verifikasi dibuka lewat modal, bukan halaman detail URL terpisah.
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
- Admin dapat mengelola tarif ini melalui menu **Master Data > Nilai Strategis Reklame**
- Field angka desimal di form backoffice menerima input `.` maupun `,`, lalu dinormalisasi saat validasi/simpan; tampilan nilai tetap mengikuti format lokal Indonesia

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
  |-- Registrasi via Mobile/API --->|                              |
  |   (OTP Email → submit data)     |                              |
  |                                 |                              |
  |                                 |                              |-- Verifikasi WP
  |                                 |                              |   (Setujui/Tolak/
  |                                 |                              |    Perlu Perbaikan)
  |                                 |                              |
  |<----- Notifikasi Status --------|<-----------------------------|
  |   (NPWPD digenerate jika disetujui)                            |
  |                                 |                              |
  |   ATAU                          |                              |
  |                                 |-- Daftarkan WP Manual ------>|
  |                                 |   (via Backoffice)           |
  |                                 |                              |
  |<----- Akun + NPWPD Langsung ----|                              |
  |   (auto-approve, wajib ganti password saat login pertama)      |
```

**Format NPWPD:** `P1XXXXXXXXXXX` (perorangan) atau `P2XXXXXXXXXXX` (perusahaan) — 13 karakter, sekuensial.

**Status Wajib Pajak:**
- `menungguVerifikasi` → Menunggu Verifikasi
- `disetujui` → Disetujui (NPWPD terbit)
- `ditolak` → Ditolak
- `perluPerbaikan` → Perlu Perbaikan (menunggu perbaikan data dari wajib pajak)

**Catatan implementasi saat ini:**
- Registrasi mandiri via mobile/API masuk ke status `menungguVerifikasi` dan diproses dari modul verifikasi WP.
- Pendaftaran WP via backoffice oleh admin/petugas langsung membuat akun portal, mengisi `tanggal_verifikasi`, mengenerate NPWPD, dan menyimpan record sebagai `disetujui` tanpa antrean verifikasi terpisah.
- Akun backoffice yang baru dibuat untuk WP diberi flag `must_change_password = true`, sehingga login pertama wajib melewati alur ganti password.

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
- **`revision_attempt_no`**: nomor internal attempt billing per masa pajak untuk audit dan unique key. Nilai ini terus naik walau `pembetulan_ke` yang tampil ke user kembali memakai nomor sebelumnya setelah attempt salah dibatalkan.
- Pembetulan yang sudah dibayar membentuk rantai (`parent_tax_id` → `children`)
- Kredit pajak = total yang sudah dibayar pada billing sebelumnya
- Saat halaman resolusi pembetulan billing dibuka oleh `admin`, `petugas`, atau `verifikator`, sistem menampilkan halaman status billing dalam layout standalone tanpa sidebar dan topbar; wajib pajak tetap melihat layout portal seperti sebelumnya.

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

### 5.7 Alur Lupa Password (Portal Web & API Mobile)

```
[Wajib Pajak]                    [Sistem]
  |                              |
  |-- Minta OTP reset ---------->|
  |   (email akun portal)        |
  |                              |
  |<-- OTP 6 digit via email ----|
  |   (3 menit, cooldown 2 menit)|
  |                              |
  |-- Verifikasi OTP ----------->|
  |                              |
  |<-- verification token -------|
  |                              |
  |-- Set password baru -------->|
  |                              |
  |<-- Password diperbarui ------|
```

- Berlaku untuk akun portal wajib pajak yang aktif dan memiliki email login.
- OTP reset password berlaku 3 menit, maksimal 3 percobaan verifikasi, dan maksimal 3 request per 15 menit.
- Request ulang OTP menonaktifkan OTP reset sebelumnya yang masih aktif.
- Jika email tidak memenuhi syarat, sistem tetap mengembalikan respons netral agar tidak membuka informasi keberadaan akun.

### 5.8 Alur Wajib Ganti Password Saat Login Pertama

```
[Wajib Pajak]                    [Portal/API]                 [Sistem]
  |                                 |                          |
  |-- Login ----------------------->|                          |
  |                                 |-- Cek must_change_password|
  |                                 |                          |
  |<-- Dialihkan / dibatasi --------|<-------------------------|
  |   ke ubah password pertama      |                          |
  |                                 |                          |
  |-- Simpan password baru -------->|------------------------->|
  |                                 |                          |
  |<-- Akses penuh dipulihkan ------|<-------------------------|
```

- Dipakai untuk akun WP hasil pendaftaran backoffice atau akun yang password-nya di-reset oleh petugas/admin.
- Di portal web, user diarahkan paksa ke `/portal/password/change-first` sebelum bisa membuka halaman portal lain.
- Di API mobile, token login tetap diterbitkan, tetapi akses dibatasi sampai user menyelesaikan `update-password`.

### 5.9 Alur Perubahan Data Wajib Pajak (Data Change Request)

```
[Admin/Petugas]                   [Verifikator/Admin]              [Sistem]
   |                                  |                            |
   |-- Edit data WP ----------------->|                            |
   |   (record sudah disetujui)       |                            |
   |                                  |                            |
   |                           [Buat DataChangeRequest pending]    |
   |                                  |                            |
   |                                  |-- Review request --------->|
   |                                  |   Setujui / Tolak          |
   |                                  |                            |
   |<----- Hasil review --------------|<---------------------------|
```

- Perubahan sensitif pada WP yang sudah `disetujui` tidak langsung mengubah record utama.
- Sistem membuat `DataChangeRequest` berstatus `pending` agar ada jejak review dan audit.
- Hanya setelah disetujui oleh admin/verifikator perubahan diterapkan ke data wajib pajak.

### 5.10 Alur Histori Pajak Publik

```
[Publik/WP]                         [Sistem]
  |                                  |
  |-- Isi NPWPD + Tahun -----------> |
  |   (captcha + rate limit)         |
  |                                  |
  |<-- Tabel histori dokumen --------|
  |   + status efektif pembayaran    |
  |                                  |
  |-- Cetak PDF histori -----------> |
  |                                  |
  |<-- PDF inline Folio/F4 ----------|
```

- Histori publik mencakup Billing, STPD Manual, Surat Ketetapan, SKPD Reklame, SKPD Air Tanah, dan SKRD Sewa Tanah.
- Endpoint PDF publik memakai route terpisah `/histori-pajak/pdf`.

### 5.11 Alur Pengajuan MBLB Portal (Self-Assessment dengan Verifikasi)

```
[Wajib Pajak]                    [Admin/Verifikator]              [Sistem]
  |                                  |                              |
  |-- Submit MBLB via portal ------->|                              |
  |   (volume per mineral + lampiran)|                              |
  |                                  |                              |
  |                            [PortalMblbSubmission status pending]|
  |                                  |                              |
  |                                  |-- Review submission -------->|
  |                                  |   Setujui / Tolak / Revisi   |
  |                                  |                              |
  |                                  |          (Setujui)           |
  |                                  |                       [Generate Billing Code]
  |                                  |                       [Hitung pokok + opsen]
  |                                  |                       [Buat record Tax]
  |                                  |                              |
  |<------------- Notifikasi hasil --|<-----------------------------|
```

- Berbeda dengan self-assessment lain, MBLB portal tidak langsung menerbitkan billing.
- Submission dan lampiran ditinjau admin/verifikator dulu sebelum billing dibuat.
- Halaman sukses portal mengarah ke `/portal/self-assessment/mblb-submissions/{submissionId}/success` selama menunggu verifikasi.

### 5.12 Alur Perpanjangan Reklame

```
[Wajib Pajak]              [Petugas]               [Verifikator]          [Sistem]
   |                           |                        |                    |
   |-- Ajukan perpanjangan --->|                        |                    |
   |   (durasi 30/90/180/365)  |                        |                    |
   |                           |                        |                    |
   |                           |-- Buat draft SKPD --->|                    |
   |                           |   perpanjangan         |                    |
   |                           |                        |                    |
   |                           |                        |-- Setujui SKPD -->|
   |                           |                        |   ATAU Tolak       |
   |                           |                        |              [Generate nomor SKPD]
   |                           |                        |              [Generate billing]
   |                           |                        |              [Update masa berlaku objek]
   |                           |                        |                    |
   |<----- Notifikasi SKPD perpanjangan -----------------|<------------------|
```

- Hanya boleh diajukan jika izin reklame ≤ 30 hari dari kadaluarsa atau sudah kadaluarsa.
- Tidak boleh ada pengajuan aktif (`diajukan`/`menungguVerifikasi`/`diproses`) yang sama untuk objek tersebut.
- Status objek reklame otomatis menjadi `kadaluarsa` saat `masa_berlaku_sampai` lewat.

### 5.13 Alur Lapor Meter Air Tanah (Mobile)

```
[Wajib Pajak Mobile]              [Petugas/Verifikator]              [Sistem]
   |                                    |                              |
   |-- Submit laporan meter ----------->|                              |
   |   (foto meter + GPS + reading)     |                              |
   |                                    |                       [Simpan MeterReport]
   |                                    |                       [Update last_meter_reading]
   |                                    |                       [Notifikasi role petugas]
   |                                    |                              |
   |                                    |-- Proses laporan ----------->|
   |                                    |   → buat draft SKPD Air Tanah|
   |                                    |                              |
   |                                    |   (lanjut ke alur SKPD       |
   |                                    |    Air Tanah pada 5.4)       |
```

- Laporan meter hanya valid untuk objek air tanah aktif milik user login.
- Setelah disubmit, laporan berstatus `submitted` dan masuk ke modul Laporan Meter backoffice.

### 5.14 Alur Gebyar Sadar Pajak

```
[Wajib Pajak]                       [Admin]                           [Sistem]
   |                                   |                                 |
   |-- Submit struk + jumlah --------->|                                 |
   |   transaksi pajak                 |                                 |
   |                                   |                          [GebyarSubmission pending]
   |                                   |                          [kupon_count default 1]
   |                                   |                                 |
   |                                   |-- Review submission ----------->|
   |                                   |   Setujui (kupon final) / Tolak |
   |                                   |                                 |
   |<--------- Notifikasi hasil -------|<--------------------------------|
```

- Submission masuk dalam status `pending` dan menunggu review admin.
- Default kupon `1`; admin dapat menyesuaikan jumlah kupon saat menyetujui.

### 5.15 Alur Lunas Bayar Manual (Backoffice)

```
[Admin]                                   [Sistem]
  |                                         |
  |-- Cari billing (kode/NPWPD) ----------->|
  |   Input pokok + sanksi + bukti bayar    |
  |                                         |
  |                                  [Buat TaxPayment]
  |                                  [Recalculate sisa tagihan]
  |                                  [Update status: paid / partially_paid]
  |                                  [Auto-issue SPTPD jika triwulan lengkap]
  |                                  [Auto-issue STPD jika sanksi & triwulan lengkap]
  |                                         |
  |<------ Konfirmasi pelunasan ------------|
```

- Hanya untuk role admin.
- Billing `expired` tetap dapat dilunaskan manual selama masih ada sisa tagihan.

### 5.16 Alur Pembatalan Pembayaran

```
[Admin]                                   [Sistem]
  |                                         |
  |-- Pilih pembayaran -------------------->|
  |   Input alasan pembatalan               |
  |                                         |
  |                                  [Soft-delete TaxPayment]
  |                                  [Recalculate sisa tagihan]
  |                                  [Revoke SPTPD jika tidak lagi lunas]
  |                                  [Revoke STPD jika pokok tidak lunas]
  |                                  [Reopen status sesuai domain]
  |                                         |
  |<------ Notifikasi pembatalan -----------|
```

- Status billing kembali ke `pending`/`verified`/`expired`/`partially_paid` sesuai sisa pembayaran dan jatuh tempo.
- Nomor SPTPD/STPD dipertahankan jika pokok tetap lunas penuh.

### 5.17 Alur STPD Manual

```
[Petugas]                       [Verifikator/Admin]                 [Sistem]
   |                                    |                              |
   |-- Pilih billing + tipe STPD ------>|                              |
   |   (pokok_sanksi / sanksi_saja)     |                              |
   |                                    |                       [Buat StpdManual draft]
   |                                    |                       [Notifikasi verifikator]
   |                                    |                              |
   |                                    |-- Setujui & Terbitkan ------>|
   |                                    |   ATAU Tolak                 |
   |                                    |                       [Generate nomor STPD resmi]
   |                                    |                       [Set kode pembayaran]
   |                                    |                       [Sync stpd_number ke billing]
   |                                    |                              |
   |<--------- Notifikasi STPD terbit --|<-----------------------------|
```

- Pembuat draft tidak boleh memverifikasi dokumennya sendiri.
- Kode pembayaran `pokok_sanksi` mengikuti billing asal; `sanksi_saja` memakai kode turunan dengan digit ke-8 dan ke-9 menjadi `77`.

### 5.18 Alur Surat Ketetapan Pajak Daerah (SKPDKB/KBT/LB/N)

```
[Admin/Petugas]              [Admin/Verifikator]                  [Sistem]
   |                                |                                |
   |-- Pilih billing sumber ------->|                                |
   |   Pilih jenis surat + dasar    |                                |
   |   Isi nominal & bulan bunga    |                                |
   |                                |                          [Buat draft TaxAssessmentLetter]
   |                                |                                |
   |                                |-- Setujui & Terbitkan -------->|
   |                                |   ATAU Tolak                   |
   |                                |                          [Generate nomor surat resmi]
   |                                |                          [SKPDKB/KBT → buat billing baru (suffix 19)]
   |                                |                          [SKPDLB → simpan saldo kredit]
   |                                |                          [SKPDN → tidak ada billing baru]
   |                                |                                |
   |<-------- Notifikasi surat terbit ------------------------------|
```

- Pembuat draft tidak boleh memverifikasi dokumennya sendiri.
- Saldo kredit `SKPDLB` dapat dialokasikan ke billing lain milik WP yang sama melalui aksi kompensasi.

### 5.19 Alur Peminjaman Aset Reklame oleh OPD

```
[Admin/Petugas]                       [Sistem]
   |                                     |
   |-- Catat peminjaman aset ----------->|
   |   (OPD peminjam, materi, periode)   |
   |                                     |
   |                              [Set status aset: dipinjam_opd]
   |                              [Catat periode pinjam]
   |                                     |
   |   (saat pinjam_selesai lewat)       |
   |                              [SyncKetersediaanAsetReklame]
   |                              [Tutup pinjam aktif]
   |                              [Set status aset: tersedia]
```

- Berbeda dari sewa komersial; tidak menerbitkan SKPD/billing.
- Pengembalian otomatis ketika `pinjam_selesai` lewat melalui scheduler `SyncKetersediaanAsetReklame`.

### 5.20 Alur Retribusi Sewa Tanah (SKRD)

```
[Petugas]                      [Verifikator/Admin]                 [Sistem]
   |                                   |                              |
   |-- Pilih wajib bayar + objek ----->|                              |
   |   Pilih sub-jenis sewa tanah      |                              |
   |   Input durasi + masa berlaku     |                              |
   |                                   |                       [Hitung Retribusi = Tarif × Durasi]
   |                                   |                       [Buat draft SkrdSewaRetribusi]
   |                                   |                              |
   |                                   |-- Setujui & Terbitkan ----->|
   |                                   |   ATAU Tolak                 |
   |                                   |                       [Generate nomor SKRD]
   |                                   |                       [Generate billing prefix 41104]
   |                                   |                              |
   |<--------- Notifikasi SKRD terbit -|<-----------------------------|
```

- Tarif tetap nominal (bukan persentase) per sub-jenis sewa tanah.
- Kode billing memakai prefix `41104` lewat `billing_kode_override`.

### 5.21 Alur Auto-Expire Billing (Scheduler)

```
[Cron tax:sync-expired-statuses]              [Sistem]
   |                                             |
   |-- Jalan tiap jam -------------------------->|
   |                                       [Cari billing pending/verified/partially_paid
   |                                        dengan payment_expired_at < now()]
   |                                       [Set status: expired]
   |                                       [Catat batch ke ActivityLog]
   |                                       [Kirim notifikasi backoffice
   |                                        (deep-link Histori Auto-Expire)]
```

- Billing `expired` tetap diperlakukan sebagai kewajiban aktif untuk pelunasan manual, STPD manual, pembetulan, dan blocking duplikat.
- Notifikasi backoffice memuat ringkasan jumlah, daftar kode billing, status asal, dan dipotong otomatis bila batch sangat besar.

### 5.22 Alur Single Session Lintas Kanal

```
[Akun WP / Backoffice]                    [Sistem]
   |                                         |
   |-- Login dari kanal baru --------------->|
   |   (portal / backoffice / mobile)        |
   |                                         |
   |                                  [Rotasi active_session_id]
   |                                  [Catat metadata kanal/IP/device/waktu]
   |                                  [Tampilkan notif "sesi sebelumnya digantikan"]
   |                                         |
   |   (request berikutnya dari sesi lama)   |
   |                                  [Tolak akses + pesan kanal baru]
   |                                  [Token API lama menjadi stale]
```

- Berlaku lintas kanal: portal web, backoffice Filament, dan API Sanctum.
- Pesan kepada user menyebutkan kanal dan perangkat tempat login baru terjadi.

### 5.23 Alur Pembatalan Billing

```
[Admin/Petugas]                                 [Sistem]
   |                                               |
   |-- Pilih billing pending/verified/expired --->|
   |   Input alasan pembatalan                     |
   |                                               |
   |                                        [Validasi: tidak ada pembayaran lunas]
   |                                        [Soft-delete Tax + relasi billing]
   |                                        [Catat alasan + petugas pembatal]
   |                                        [ActivityLog: billing_cancelled]
   |                                               |
   |   (opsional pemulihan)                        |
   |-- Restore billing dibatalkan ---------------->|
   |                                        [Restore record + reset status awal]
   |                                        [ActivityLog: billing_restored]
```

- Berbeda dengan Pembatalan Pembayaran (5.16) — ini membatalkan billing yang belum lunas, bukan transaksi pembayaran.
- Billing yang sudah ada pembayaran lunas/sebagian tidak dapat dibatalkan; harus melalui Pembatalan Pembayaran terlebih dulu.
- Riwayat pembatalan tetap tersimpan dan dapat dilihat di halaman Pembatalan Billing.

### 5.24 Alur Pembetulan SPT/SPTPD

```
[WP Portal/Mobile atau Petugas]      [Verifikator]                [Sistem]
   |                                      |                          |
   |-- Ajukan pembetulan billing -------->|                          |
   |   (alasan + nilai usulan + lampiran) |                          |
   |                                      |                  [PembetulanRequest pending]
   |                                      |                          |
   |                                      |-- Review usulan -------->|
   |                                      |   Setujui / Tolak        |
   |                                      |                          |
   |                                      |   (Setujui)              |
   |                                      |                  [Update nilai pajak terhutang]
   |                                      |                  [Recalculate sisa & sanksi]
   |                                      |                  [Catat versi pembetulan]
   |                                      |                          |
   |<-------- Notifikasi hasil -----------|<-------------------------|
```

- Pembetulan hanya valid untuk billing yang sudah diterbitkan dan belum lunas penuh.
- Histori nilai sebelum/sesudah pembetulan disimpan untuk audit trail.

### 5.25 Alur Permohonan Sewa Reklame Publik

```
[Publik / Calon Penyewa]              [Admin/Petugas]                 [Sistem]
   |                                       |                             |
   |-- Pilih aset reklame Pemkab --------->|                             |
   |   Isi data pemohon + materi + periode |                             |
   |   Unggah desain materi                |                             |
   |                                       |                      [PermohonanSewaReklame pending]
   |                                       |                      [Aset: status pending sewa]
   |                                       |                             |
   |                                       |-- Review permohonan ------>|
   |                                       |   Setujui / Tolak           |
   |                                       |                             |
   |                                       |   (Setujui)                 |
   |                                       |                      [Buat SKPD Reklame]
   |                                       |                      [Generate billing]
   |                                       |                      [Aset: status disewa]
   |                                       |                             |
   |<--------- Notifikasi + tagihan -------|<----------------------------|
```

- Hanya aset reklame Pemkab berstatus `tersedia` yang dapat diajukan publik.
- Permohonan yang ditolak/expired mengembalikan status aset ke `tersedia`.
- Setelah masa sewa berakhir, status aset kembali `tersedia` melalui scheduler `SyncKetersediaanAsetReklame`.

**Narasi rinci alur permohonan sampai pembayaran:**
- Publik hanya dapat mengajukan sewa pada aset reklame Pemkab yang masih berstatus `tersedia`.
- Pemohon memilih aset, lalu mengisi identitas, `jenis_reklame_dipasang`, durasi sewa, satuan sewa (`minggu`, `bulan`, atau `tahun`), tanggal mulai yang diinginkan, nomor registrasi izin, catatan tambahan, serta mengunggah dokumen pendukung termasuk desain materi reklame.
- Sistem memvalidasi ketersediaan aset, batas maksimal durasi per satuan, dan mencegah duplikasi permohonan aktif untuk kombinasi aset yang sama dan NIK yang sama.
- Jika lolos validasi, sistem membuat `PermohonanSewaReklame` dengan nomor tiket otomatis dan status awal `diajukan`.
- Petugas atau admin mengambil permohonan untuk diproses sehingga status berubah menjadi `diproses`, `tanggal_diproses` terisi, dan identitas petugas penangan tersimpan.
- Pada tahap review, petugas memastikan pemohon sudah memiliki NPWPD. Jika belum ada, petugas dapat membuat NPWPD baru dari data permohonan. Jika sudah ada, petugas dapat menautkan NPWPD yang ditemukan ke permohonan.
- Jika data atau dokumen belum lengkap, petugas dapat mengembalikan permohonan ke status `perlu_revisi` dengan `catatan_petugas`. Pemohon kemudian memperbaiki data dan mengajukan ulang, sehingga status kembali ke `diajukan`.
- Jika permohonan tidak dapat dilanjutkan, petugas dapat menolak permohonan. Status berubah menjadi `ditolak`, `tanggal_selesai` diisi, alasan penolakan disimpan, dan alur berhenti tanpa SKPD maupun billing.
- Jika permohonan dinyatakan layak dan NPWPD sudah tersedia, petugas membuat draft `SKPD Reklame` dari data permohonan dan aset yang dipilih.
- Pada pembuatan draft SKPD sewa aset Pemkab, sistem memetakan jenis aset ke master reklame, menentukan satuan waktu, menghitung durasi dan masa berlaku, lalu menyimpan draft SKPD dengan relasi ke `permohonan_sewa_id`.
- Untuk SKPD sewa aset Pemkab, `nama_reklame` tetap memakai nama aset reklame Pemkab, sedangkan materi iklan yang diajukan publik disimpan terpisah di field `isi_materi_reklame` dari nilai `jenis_reklame_dipasang`.
- Setelah draft SKPD terbentuk, permohonan tetap berstatus `diproses` sambil menunggu verifikasi SKPD oleh verifikator.
- Verifikator meninjau draft SKPD. Jika draft ditolak, status SKPD menjadi `ditolak` dan permohonan yang terhubung ikut diperbarui menjadi `ditolak`.
- Jika draft SKPD disetujui, sistem menerbitkan nomor SKPD final, menghasilkan `kode_billing`, menghitung `jatuh_tempo`, dan mengubah status SKPD menjadi `disetujui`.
- Pada saat SKPD reklame hasil permohonan sewa disetujui, sistem otomatis membuat record `Tax` sebagai billing resmi dengan status awal `verified`. Status ini berarti tagihan sudah terbit dan menunggu pembayaran.
- Pada saat yang sama, observer SKPD juga memperbarui `PermohonanSewaReklame` menjadi `disetujui`, mengisi `tanggal_selesai`, dan menautkan `skpd_id` ke permohonan tersebut.
- Sinkronisasi ketersediaan aset berjalan otomatis. Karena aset sudah memiliki SKPD aktif hasil sewa, status aset berubah dari `tersedia` menjadi `disewa`.
- Setelah billing terbit, wajib pajak dapat melihat detail billing, kode billing, nominal tagihan, dan status pembayaran melalui kanal yang tersedia di sistem.
- Pelunasan dilakukan melalui flow `Lunas Bayar Manual` oleh admin. Admin mencari billing berdasarkan kode billing atau NPWPD, lalu menginput jumlah pembayaran, tanggal bayar, lokasi, referensi, dan bukti bayar.
- Sistem membuat record `TaxPayment` untuk setiap pembayaran yang masuk dan menghitung sisa kewajiban secara otomatis.
- Jika nominal pembayaran menutup seluruh tagihan, status billing berubah dari `verified` menjadi `paid`.
- Jika nominal pembayaran baru menutup sebagian tagihan, status billing berubah menjadi `partially_paid`.
- Billing yang sudah melewati jatuh tempo dapat berubah menjadi `expired`, tetapi tetap dapat dilunasi selama masih ada sisa kewajiban.
- Untuk alur permohonan sewa reklame Pemkab, titik akhir proses pengajuan berada pada status permohonan `disetujui` saat SKPD resmi diterbitkan, sedangkan titik akhir proses keuangan berada pada status billing `paid` saat seluruh tagihan sudah lunas dibayarkan.

**Ringkasan status per entitas:**
- `PermohonanSewaReklame`: `diajukan` → `diproses` → `perlu_revisi` atau `ditolak` atau `disetujui`
- `SKPD Reklame`: `draft` → `disetujui` atau `ditolak`
- `Billing/Tax`: `verified` → `paid` atau `partially_paid` atau `expired`
- `Aset Reklame Pemkab`: `tersedia` → `disewa` → kembali `tersedia` setelah masa sewa berakhir dan sinkronisasi ketersediaan dijalankan

### 5.26 Alur Buat Billing Self-Assessment Backoffice

```
[Admin/Petugas]                          [Sistem]
   |                                        |
   |-- Pilih wajib pajak + objek pajak --->|
   |   Pilih jenis pajak self-assessment   |
   |   Input dasar pengenaan + masa pajak  |
   |                                        |
   |                                 [Hitung pokok + sanksi]
   |                                 [Generate kode billing]
   |                                 [Set payment_expired_at]
   |                                 [Catat petugas pembuat]
   |                                        |
   |<------ Billing terbit -----------------|
```

- Counterpart backoffice dari self-assessment portal/mobile; biasanya dipakai saat WP datang ke loket.
- Berlaku untuk pajak self-assessment umum (Restoran, Hotel, Hiburan, Parkir, PJU, dsb.).

### 5.27 Alur Buat Billing Sarang Walet

```
[Petugas]                              [Sistem]
   |                                      |
   |-- Pilih WP + objek sarang walet --->|
   |   Input volume produksi + jenis      |
   |                                      |
   |                              [Cari HargaPatokanSarangWalet aktif]
   |                              [Pokok = Volume × Harga × Tarif]
   |                              [Generate billing + kode pembayaran]
   |                                      |
   |<------ Billing terbit ---------------|
```

- Harga patokan sarang walet ditentukan per jenis dan tahun aktif.
- Tarif efektif mengikuti pengaturan jenis/sub-jenis pajak terkait.

### 5.28 Alur Buat Billing MBLB Backoffice

```
[Admin/Petugas]                        [Sistem]
   |                                      |
   |-- Pilih WP + lokasi tambang ------->|
   |   Input volume per mineral           |
   |                                      |
   |                              [Cari HargaPatokanMblb aktif per mineral]
   |                              [Pokok = Σ(Volume × HPP × Tarif)]
   |                              [Hitung opsen jika berlaku]
   |                              [Generate billing langsung terbit]
   |                                      |
   |<------ Billing terbit ---------------|
```

- Counterpart backoffice dari 5.11 MBLB Portal — di sini billing terbit langsung tanpa antrian verifikasi pengajuan.
- Harga patokan MBLB diatur per mineral dan periode aktif.

### 5.29 Alur Akses Dokumen Pribadi Wajib Pajak

```
[Wajib Pajak Login (Portal/Mobile)]            [Sistem]
   |                                              |
   |-- Buka Daftar SKPD Saya / Riwayat Dok. ---->|
   |                                       [Filter dokumen by user_id WP]
   |                                       [Scope hanya milik akun login]
   |                                              |
   |<-- Daftar SKPD/STPD/Surat Ketetapan/SKRD ---|
   |                                              |
   |-- Pilih dokumen → Preview/Cetak ------------>|
   |                                       [Validasi kepemilikan + status terbit]
   |                                              |
   |<-- PDF inline / preview HTML ---------------|
```

- Menggantikan kebutuhan akses publik untuk WP yang sudah login; tidak butuh captcha.
- Mencakup SKPD Reklame, SKPD Air Tanah, STPD Manual, Surat Ketetapan, dan SKRD Sewa Tanah.
- WP hanya dapat melihat dokumen yang `user_id`-nya cocok dengan akun login.

### 5.30 Alur Notifikasi & Deep-link Backoffice

```
[Sistem / Event Domain]                      [Backoffice User]
   |                                              |
   |-- Trigger event (auto-expire,                |
   |   DCR baru, MBLB pending, Gebyar             |
   |   pending, pembetulan, dll.) ---------->     |
   |                                              |
   |   [Buat DatabaseNotification per role        |
   |    target: admin/verifikator/petugas]        |
   |   [Sertakan URL deep-link]                   |
   |                                              |
   |                                       [Bell icon Filament: badge +1]
   |                                              |
   |                                  <-- Klik notifikasi --
   |                                              |
   |   [Redirect ke halaman terkait               |
   |    (resource list / detail / histori)]       |
   |   [Mark notifikasi sebagai dibaca]           |
```

- Notifikasi backoffice memakai database channel Laravel + tampilan Filament bell.
- Setiap event memilih role target yang relevan agar tidak membanjiri user lain.
- Deep-link mengarah langsung ke konteks (mis. Histori Auto-Expire, daftar DCR pending, MBLB submission detail).

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
- **Aksi Cepat:** shortcut ke `Laporan Pendapatan` (`/admin/laporan-pendapatan`), `Wajib Pajak`, `Kelola Data Objek Pajak`, `Buat Billing Self Assessment`, `Buat Billing MBLB`, `Buat Billing Sarang Burung Walet`, `Buat SKPD Reklame`, dan `Buat SKPD Air Tanah` dengan visibilitas mengikuti akses role halaman tujuan
- **Blok Perlu Tindakan:** shortcut verifikasi dengan counter untuk Wajib Pajak, Pengajuan Reklame Portal, Permintaan Pembetulan, SKPD Reklame, dan SKPD Air Tanah jika ada item menunggu sesuai akses role
- **Chart Line:** Tren pendapatan 6 bulan terakhir
- **Visual pendapatan per jenis pajak:** ringkasan bulan ini per jenis pajak dalam bentuk bar/progress list
- **Daftar:** transaksi terbaru dengan link ke halaman Laporan Pendapatan

### 6.2 Halaman Buat Billing

#### Buat Billing Self-Assessment
- **Jenis pajak:** Hotel (41101), Restoran (41102), Hiburan (41103), PPJ (41105), Parkir (41107)
- **Fitur:** Pencarian NIK/NPWPD/nama, auto-deteksi masa pajak berikutnya, input omzet, deteksi duplikat, dan konfirmasi pembetulan atau penggantian billing sesuai status tagihan yang sudah ada
- **Input desimal fleksibel:** field desimal pada self-assessment portal dan halaman backoffice `Buat Billing Self-Assessment` menerima titik (`.`) maupun koma (`,`); sistem menormalisasi nilai ke format numerik baku sebelum validasi dan penyimpanan, sedangkan tampilan hasil/perhitungan tetap memakai format Indonesia
- **Instansi opsional:** untuk objek `is_opd` petugas dapat memilih OPD/instansi/lembaga terkait; field pilihan mendukung pencarian agar lebih mudah dipakai saat data instansi banyak, dan nilainya disimpan sebagai snapshot pada billing
- **Warning lompat periode:** Untuk objek reguler bulanan, petugas tetap boleh memilih masa pajak yang melompati prefill periode berikutnya, tetapi sistem menampilkan konfirmasi khusus bahwa masa pajak sebelumnya belum dibuat
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
- **Input desimal fleksibel:** volume per mineral menerima titik (`.`) maupun koma (`,`), lalu dinormalisasi sebelum validasi, preview kalkulasi, dan penerbitan billing. Ringkasan nominal tetap tampil dengan format Indonesia.
- **Portal WP:** submit sebagai pengajuan verifikasi; billing code baru diterbitkan setelah admin/verifikator menyetujui pengajuan dan meninjau lampiran
- **Instansi opsional:** untuk sub-jenis `MBLB_WAPU`, petugas maupun wajib pajak dapat memilih instansi terkait; field di backoffice mendukung pencarian agar pemilihan instansi besar lebih cepat, dan snapshot-nya ikut dibawa sampai billing disetujui/diterbitkan
- **Aturan prefill masa pajak:**
  - `MBLB_WAPU` → selalu prefill **bulan berjalan**
  - `MBLB_WP` → prefill **bulan setelah billing aktif terakhir** berdasarkan `nopd`
  - Jika objek `MBLB_WP` belum punya histori billing aktif pada `nopd` tersebut, fallback ke **bulan berjalan**

#### Buat Billing Sarang Walet
- **Jenis pajak:** Sarang Burung Walet (41109)
- **Fitur:** Pilih jenis sarang, input volume (kg), masa pajak tahunan
- **Input desimal fleksibel:** field volume (kg) menerima titik (`.`) maupun koma (`,`), lalu dinormalisasi sebelum validasi, preview perhitungan, dan penerbitan billing. Tampilan ringkasan tetap mengikuti format Indonesia.

### 6.3 Halaman Buat SKPD

#### Buat SKPD Air Tanah
- **Fitur:** Pilih objek air tanah, 4 skenario meter (baru/tanpa meter/ganti meter/normal), lookup NPA bertingkat (tiered), perhitungan pajak otomatis, notifikasi ke verifikator
- **Layout preview:** panel rincian perhitungan dan tombol aksi di sisi kanan tetap bergerak sebagai satu blok pada layar besar agar tidak overlap saat discroll ke bagian bawah
- **Input desimal fleksibel:** Field meter dan pemakaian air menerima input desimal dengan titik (`.`) maupun koma (`,`), lalu dinormalisasi ke format internal sebelum preview pemakaian, perhitungan NPA, dan penyimpanan draft. Tampilan angka tetap memakai format Indonesia.

#### Buat SKPD Reklame
- **Dua mode:** berbasis objek WP atau aset Pemkab
- **Mode Objek WP:** Pilih sub-jenis pajak (tetap/insidentil), kelompok lokasi, satuan waktu, dimensi, jumlah, lokasi penempatan, jenis produk, isi materi reklame opsional, perhitungan tarif dinamis + nilai strategis + penyesuaian. Nilai ini disimpan terpisah sebagai data tambahan SKPD, tidak menggantikan nama reklame objek.
- **Mode Aset Pemkab (Simplified):**
  - Step 1: Cari & pilih aset reklame milik Pemkab
  - Step 2: Cari & pilih wajib pajak (penyewa) berdasarkan NPWPD, NIK, atau nama
  - Step 3: Pilih satuan waktu (harga sewa per minggu/bulan/tahun otomatis dari data aset)
  - Step 4: Isi durasi dan masa berlaku mulai
  - Perhitungan: Harga sewa × durasi (tarif tetap, tanpa lookup tarif/penyesuaian)
  - Field yang di-hide: sub jenis pajak, kelompok lokasi, lokasi penempatan, jenis produk, jumlah reklame, luas, jumlah muka
  - Preview: Menampilkan harga sewa per periode, durasi, dan total pajak
- **Permohonan sewa online:** Data WP dan aset diisi otomatis dari permohonan, materi reklame pada SKPD disimpan ke field tambahan `isi_materi_reklame` dari `jenis_reklame_dipasang` permohonan online, perhitungan tetap menggunakan metode harga tetap

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

Catatan pemisahan peran verifikasi:
- Pembuat draft dokumen tidak boleh memverifikasi dokumennya sendiri. Draft SKPD Reklame, SKPD Air Tanah, STPD manual, dan surat ketetapan harus diverifikasi oleh user admin/verifikator yang berbeda dari pembuat draft.

Catatan implementasi saat ini:
- Edit data identitas Wajib Pajak oleh admin/petugas tidak langsung mengubah entity, tetapi membuat `DataChangeRequest` berstatus `pending`.
- Verifikator/admin kemudian mereview request tersebut dari modul `Data Change Request` untuk `approve` atau `reject`.
- Edit record Wajib Pajak yang sudah `disetujui` tetap tersedia untuk admin/petugas, tetapi perubahan sensitifnya tetap masuk ke workflow `DataChangeRequest` agar ada jejak review.
- Aksi verifikasi Wajib Pajak (`Setujui`, `Tolak`, `Perlu Perbaikan`) digunakan untuk record berstatus `menungguVerifikasi` dan dijalankan oleh admin/verifikator.
- Pendaftaran WP manual dari modul backoffice admin/petugas tidak masuk antrean ini; flow tersebut langsung mengisi status `disetujui`, NPWPD, dan metadata verifikasi saat record dibuat.
- Persetujuan SKPD Reklame tetap satu tahap, tetapi sinkronisasi perubahan ke `tax_objects` sekarang menulis `ActivityLog` hanya untuk field objek yang benar-benar berubah, lengkap dengan nomor SKPD draft/final, `request_id` bila berasal dari pengajuan, nama petugas pembuat draft, dan nama verifikator penyetuju.
- Widget riwayat perubahan objek pajak menampilkan label aksi log yang human-readable, sehingga proses seperti sinkronisasi objek dari persetujuan SKPD Reklame tidak lagi tampil sebagai kode action internal.

### 6.11 Surat Ketetapan Pajak Daerah
- **Akses list:** Admin, Verifikator, Petugas
- **Akses create/edit draft:** Admin, Petugas
- **Akses approve/reject:** Admin, Verifikator
- **Navigasi:** Verifikasi → Surat Ketetapan
- **Jenis dokumen:** `SKPDKB`, `SKPDKBT`, `SKPDLB`, `SKPDN`
- **Flow draft:** Pilih billing sumber → pilih jenis surat → pilih dasar penerbitan → isi nominal dasar dan bulan bunga → simpan draft
- **Input nominal fleksibel:** Field nominal dasar draft dan nominal kompensasi `SKPDLB` menerima input desimal dengan titik (`.`) maupun koma (`,`), lalu dinormalisasi sebelum perhitungan bunga, kenaikan, total ketetapan, dan alokasi kredit.
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
- **Status overdue:** Billing `expired` tetap dapat dicari dan dilunaskan manual selama masih ada sisa tagihan
- **Observer/model otomatis:** `sptpd_number` diterbitkan saat billing menjadi `paid` dan syarat `isTriwulanComplete()` terpenuhi; `stpd_number` otomatis diterbitkan saat billing memiliki sanksi dan syarat `isTriwulanComplete()` juga sudah terpenuhi
- **Catatan STPD:** Billing `partially_paid` tidak lagi menerbitkan STPD otomatis sebelum triwulan lengkap; untuk kebutuhan penagihan sebelum itu gunakan flow STPD manual

### 6.7 Pembatalan Pembayaran
- **Akses:** Admin only
- **Flow:** Cari billing → lihat daftar pembayaran → pilih dan batalkan (with reason)
- **Efek:** Soft-delete pembayaran, recalculate sisa tagihan, revoke SPTPD jika tidak lagi lunas, revoke STPD jika pokok tidak lagi lunas
- **Aturan status buka kembali:** Jika semua pembayaran dibatalkan, billing kembali ke status open yang sesuai domain: `pending` untuk self-assessment yang belum jatuh tempo, `verified` untuk official assessment yang belum jatuh tempo, dan `expired` jika jatuh tempo sudah lewat. Jika masih ada sebagian pembayaran tersisa, status tetap `partially_paid`.
- **Retensi dokumen saat rollback parsial:** Nomor `SPTPD` dan `STPD` tetap dipertahankan selama pokok pajak masih lunas penuh. Karena itu rollback sanksi yang menyisakan pokok tetap lunas masih dapat menampilkan dokumen turunan walau status billing kembali ke `partially_paid`.
- **Portal dokumen pasca rollback sanksi:** Billing `partially_paid` yang masih menyimpan pokok lunas penuh tetap menampilkan aksi `SPTPD` dan `STPD` di portal selama nomor dokumen dan syarat STPD manualnya masih valid.

### 6.8 Daftar SKPD Saya (Petugas)
- **Akses:** Petugas only
- **Tab:** Air Tanah / Reklame
- **Fitur:** Lihat semua SKPD yang dibuat petugas bersangkutan, cetak/unduh PDF, revisi & ajukan ulang (jika ditolak)
- **Revisi SKPD Reklame:** Field dimensi reklame di modal revisi menerima input desimal dengan titik (`.`) maupun koma (`,`), lalu dinormalisasi sebelum validasi, hitung ulang luas, lookup tarif, dan simpan ulang draft. Tampilan nominal tetap mengikuti format Indonesia.
- **Revisi SKPD Air Tanah:** Field `Meter Awal` dan `Meter Akhir` di modal revisi menerima input desimal dengan titik (`.`) maupun koma (`,`), lalu dinormalisasi sebelum validasi, hitung ulang pemakaian, dan simpan ulang draft. Tampilan nominal tetap mengikuti format Indonesia.

### 6.9 Laporan Pendapatan
- **View tahun:** Ringkasan semua tahun (2019–sekarang) — total transaksi, total pendapatan, pending
- **View per tahun:** Detail per jenis pajak — total transaksi, total pendapatan, pendapatan bulan ini, pending
- **Filter tambahan:** tabel transaksi mendukung kolom/filter `Instansi` dan `Kategori Instansi` untuk billing yang memakai metadata instansi terkait

### 6.10 Buat STPD Manual (Petugas)
- **Akses:** Admin, Petugas, Verifikator
- **Navigasi:** Laporan Petugas → Buat STPD
- **Flow:** Cari billing by kode (18 digit) atau NPWPD (13 digit) → Pilih tipe STPD → Isi parameter → Buat draft
- **Status overdue:** Billing `expired` tetap valid untuk flow STPD manual selama masih ada tagihan yang bisa ditagih
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
- **Auto-approve backoffice:** Pendaftaran WP dari modul backoffice langsung membuat record `disetujui`, mengisi `tanggal_verifikasi`, menetapkan petugas pembuat sebagai verifikator pada record, dan langsung mengenerate NPWPD tanpa menunggu review terpisah
- **Akun portal hasil pendaftaran:** Jika email belum ada, petugas dapat tetap membuat akun WP; user portal dibuat dalam status auth `verified`, diberi password awal operasional, dan diwajibkan mengganti password saat login pertama (`must_change_password = true`)
- **Email login otomatis:** Jika email dikosongkan saat pendaftaran WP backoffice, sistem membuat email login yang tetap terbaca dari kombinasi nama, alamat, nomor kontak, dan suffix acak agar WP tetap bisa login memakai email atau NIK
- **Label UI email login:** Setelah akun dibuat, notifikasi backoffice dan form data WP menandai apakah akun memakai `Username login otomatis` atau tetap memakai email asli wajib pajak
- **Badge tabel/detail WP:** Tabel pendaftaran WP dan tabel/detail data WP menampilkan badge warna `Username Otomatis` atau `Email WP` agar operator bisa membedakan sumber username login tanpa membuka form edit
- **Objek Pajak:** Pendaftaran objek pajak baru hanya untuk admin dan petugas, dengan form kondisional per jenis pajak (reklame: bentuk+dimensi, air tanah: kelompok+kriteria, dll)
- **Detail objek pajak:** Halaman view objek pajak backoffice menampilkan lampiran foto/file objek dalam mode read-only dengan preview file tersimpan atau tautan buka file, sehingga halaman detail tidak lagi memuat kontrol upload yang hanya relevan untuk create/edit
- **URL preview foto/lampiran:** Tautan preview foto objek pajak dan lampiran SKPD Air Tanah dibangkitkan dari host request aktif (`asset('storage/...')`) sehingga preview tetap memakai domain backoffice yang sedang dipakai dan tidak terpengaruh `APP_URL` produksi yang masih `http://localhost`

---

## 7. Fitur Portal Wajib Pajak (Web)

- **Wajib ganti password saat login pertama:** user wajib pajak yang dibuat atau di-reset dari backoffice dengan flag `must_change_password = true` akan diarahkan paksa ke halaman ubah password pertama kali sebelum bisa mengakses halaman portal lain.
- **Lupa password via OTP email:** halaman login portal menyediakan tautan `Lupa password?` yang membuka alur guest untuk meminta OTP 6 digit ke email akun portal aktif, memverifikasi OTP, lalu menetapkan password baru tanpa harus login.
- **Eligibilitas lupa password:** hanya akun portal wajib pajak yang aktif dan memiliki email login yang benar-benar menerima OTP; untuk email yang tidak eligible, UI tetap menampilkan respons netral.
- **Route first-login password change:** `/portal/password/change-first`
- **Route ubah password reguler:** `/portal/password/change`
- **Route lupa password portal:** `/lupa-password`, `/lupa-password/verifikasi`, dan `/lupa-password/reset`
- **Resend OTP portal:** halaman `/lupa-password/verifikasi` menyediakan tombol `Kirim ulang OTP` yang memanggil route guest `POST /lupa-password/verifikasi/kirim-ulang` memakai email akun yang sedang diverifikasi, tetap tunduk pada cooldown 2 menit dan batas 3 request per 15 menit.
- **Indikator status password di portal:** header dan sidebar menampilkan waktu terakhir password diubah; bila password belum pernah diubah, portal menampilkan badge warning yang lebih tegas, tooltip ajakan untuk segera memperbarui password, serta animasi pulse halus dan ikon penanda khusus di mobile agar lebih cepat tertangkap.
- **Konsistensi CTA di halaman ubah password:** halaman `/portal/password/change` juga menampilkan ringkasan status password dan warning card yang selaras dengan indikator portal ketika password belum pernah diubah.
- **Style tombol ubah password reguler konsisten:** CTA `Simpan Perubahan Password` pada halaman `/portal/password/change` memakai style tombol portal utama yang sama dengan halaman auth publik agar tampil seragam di seluruh alur password.
- **Bahasa visual first-login konsisten:** halaman `/portal/password/change-first` memakai status banner dan warning card yang sama agar alur first-login dan settings reguler tetap terasa satu sistem.

### 7.1 Dashboard Portal
- Total tagihan belum dibayar dihitung dari sisa kewajiban billing milik WP yang login, termasuk billing `partially_paid`
- Total sudah dibayar mengikuti nominal billing `paid` / `verified` milik WP yang login
- Jumlah objek pajak aktif mengikuti data WP yang login melalui NPWPD portalnya
- Transaksi terbaru (5 terakhir)
- Kupon undian Gebyar
- Bell notifikasi portal memuat notifikasi milik user login saja; badge unread berkurang per notifikasi yang benar-benar dibuka atau ditandai dibaca, dan item yang membawa target URL dapat langsung diklik untuk menuju halaman konteks terkait seperti histori, daftar billing, atau detail pengajuan

### 7.2 Self-Assessment (Wizard 4 Langkah)
- **Route utama:** `/portal/self-assessment`
- **Route daftar pengajuan MBLB:** `/portal/pengajuan-mblb`
- **Route detail pengajuan MBLB:** `/portal/pengajuan-mblb/{submissionId}`
- **Route perbaiki pengajuan MBLB ditolak:** `/portal/pengajuan-mblb/{submissionId}/perbaiki`
- **Route form per jenis pajak:** `/portal/self-assessment/{jenisPajakId}/create`
- **Route hasil sukses:** `/portal/self-assessment/{taxId}/success`
- **Route hasil sukses pengajuan MBLB:** `/portal/self-assessment/mblb-submissions/{submissionId}/success`
- **Judul halaman browser:** mengikuti jenis pajak yang sedang dibuka pada form self-assessment portal
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
  - Untuk field desimal (PPJ Non-PLN, Sarang Walet, dan MBLB), portal menerima input dengan titik atau koma lalu menormalkannya sebelum submit
  - Upload lampiran wajib pada flow portal, dengan preview dokumen sebelum submit
  - Gambar lampiran yang melebihi 1 MB dikompres otomatis di browser sebelum dikirim; PDF tetap dibatasi 1 MB
3. **Sistem menghitung:**
  - Lookup tarif berlaku
  - Hitung pajak terutang sesuai tipe form yang dipilih
  - Deteksi duplikat billing pada masa pajak yang sama
  - Generate kode billing 18 digit untuk flow standard, PPJ, dan Sarang Walet
  - Khusus MBLB portal: simpan submission menunggu verifikasi admin/verifikator sebelum billing diterbitkan
  - Hitung jatuh tempo
4. **Halaman sukses** — tampilkan billing code + detail

### 7.3 Pembetulan
- **Route daftar pembetulan:** `/portal/pembetulan`
- Ajukan pembetulan untuk billing yang sudah ada (pending/paid/verified)
- Side navigation portal menyediakan menu **Ajukan Pembetulan** agar WP bisa memilih billing aktif terbaru kapan saja, tidak hanya dari halaman sukses setelah membuat billing
- Daftar billing pembetulan di portal menggunakan pagination agar tetap ringan saat data WP sudah banyak, dan filter pencarian tetap dipertahankan di URL saat pindah halaman
- Form: alasan, omzet baru, lampiran (opsional)
- Form pembetulan portal menampilkan preview dokumen sebelum submit, dan gambar lampiran di atas 1 MB dikompres otomatis di browser sebelum dikirim; PDF tetap dibatasi 1 MB
- Guard pengajuan: satu billing tidak boleh memiliki lebih dari satu `PembetulanRequest` berstatus `pending`
- Lampiran portal bersifat opsional, menerima JPG/PNG/PDF maksimal 1 MB
- Review pembetulan dilakukan dari backoffice oleh admin/petugas sampai menghasilkan billing pengganti atau penolakan

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
- Pada portal terautentikasi `/portal/cek-billing`, billing `paid` yang sudah memiliki `sptpd_number` menampilkan aksi `Cetak SPTPD` dan `Unduh SPTPD`; jika `stpd_number` juga tersedia dan objek bukan OPD, portal menampilkan aksi STPD tambahan.
- Jika billing sudah `paid` tetapi `sptpd_number` belum terbit, portal tetap menampilkan aksi billing biasa disertai catatan bahwa SPTPD belum tersedia karena dokumen triwulan belum lengkap.
- Jika billing yang dicek sudah punya pembetulan lebih baru, portal mempertahankan aksi resolusi dokumen historis alih-alih langsung mengganti tombol ke SPTPD historis.

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
- Endpoint portal notifikasi hanya dapat membaca dan mengubah notifikasi milik wajib pajak yang sedang login; akses ke notifikasi user lain akan ditolak
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
- `POST /api/v1/auth/forgot-password/request` mengirim OTP reset password 6 digit ke email akun portal aktif; OTP berlaku 3 menit, cooldown 2 menit, maksimal 3 request per 15 menit
- `POST /api/v1/auth/forgot-password/resend-otp` mengirim ulang OTP reset password untuk email yang sama dengan aturan cooldown dan rate limit yang identik dengan request awal, sekaligus menonaktifkan OTP reset sebelumnya yang masih aktif
- `POST /api/v1/auth/forgot-password/verify-otp` memakai `email` + `code` 6 digit dan mengembalikan `verification_token` untuk langkah reset password akhir
- `POST /api/v1/auth/forgot-password/reset` membutuhkan `verification_token`, `password`, dan `password_confirmation`; jika valid sistem mengganti password user, membersihkan flag `must_change_password`, dan mengosongkan token verifikasi sekali pakai
- `POST /api/v1/register` membutuhkan `verification_token` hasil verifikasi OTP serta data identitas lengkap, termasuk kode wilayah dan `password_confirmation`; user baru dibuat dengan role default `user`
- `POST /api/v1/login` memakai field `email`, tetapi nilainya boleh berupa email atau NIK
- Respons login dan profile juga mengembalikan flag `must_change_password` dan `password_changed_at`
- Jika `must_change_password = true`, token login tetap diterbitkan, tetapi sampai password diganti akses API dibatasi hanya ke `GET /api/v1/profile`, `POST /api/v1/update-password`, dan `POST /api/v1/logout`
- Respons login, profile, dan error blokir API memuat kontrak `auth_requirements` dengan `error_code = PASSWORD_CHANGE_REQUIRED`, `required_action`, dan `allowed_actions` agar aplikasi mobile bisa langsung menampilkan layar wajib ganti password
- Copywriting warning mobile mengikuti portal web agar konsisten lintas kanal: status `Belum pernah diubah`, CTA `Aksi wajib sekarang`, dan pesan aksi `Password harus diganti sebelum melanjutkan.`
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
| Landing Page | `/` | Halaman utama dengan akses cepat ke layanan publik, termasuk Cek Billing dan Histori Pajak |
| Login Portal | `/login` | Form login portal wajib pajak |
| Cek Billing | `/cek-billing` | Cek status billing berdasarkan kode, tersedia dari menu `Layanan Publik` di landing page dan memakai sub-navigation layanan publik yang sama dengan halaman publik lainnya |
| Histori Pajak | `/histori-pajak` | Cek seluruh dokumen pajak (Billing, STPD Manual, Surat Ketetapan, SKPD Reklame, SKPD Air Tanah, SKRD Sewa Tanah) per NPWPD + tahun (dilindungi captcha Cloudflare Turnstile, rate limit 5x/15 menit per IP, audit log), menampilkan jatuh tempo, tanggal bayar, dan status efektif `Menunggu Pembayaran` / `Lewat Jatuh Tempo`, serta mendukung salin data tabular langsung dari tabel untuk paste ke Excel dan cetak PDF inline via DomPDF (Folio/F4 landscape), tersedia dari menu `Layanan Publik` di landing page dan memakai sub-navigation layanan publik yang sama dengan halaman publik lainnya |
| PDF Histori Pajak | `/histori-pajak/pdf` | Generate PDF inline histori pajak publik berdasarkan NPWPD dan tahun yang sudah lolos validasi form/captcha |
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
| Info & Peta | `/sewa-reklame` | Daftar aset reklame Pemkab tersedia + peta + tarif sewa mingguan/bulanan/tahunan per aset, termasuk tautan langsung ke Google Maps untuk aset yang memiliki koordinat |
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
| Konten | Data WP, objek pajak, masa pajak, kode billing, DPP, pokok pajak, sanksi, total tagihan, jatuh tempo, dan instansi terkait bila ada |
| Konten Khusus | Detail MBLB (mineral per item), Sarang Walet (jenis + volume), PPJ (detail kapasitas) |
| Akses | Portal WP, Backoffice (cetak/unduh) |
| Route Status | `/portal/billing/{taxId}/status` mengarahkan ke SPTPD jika billing sudah lunas dan `sptpd_number` tersedia; selain itu tetap ke billing document |
| Pembetulan QR | Jika billing yang discan sudah punya rantai pembetulan yang lebih baru, route status tidak langsung redirect. Sistem menampilkan halaman resolusi yang menjelaskan dokumen asal, billing pembetulan terbaru yang berlaku, dan tombol ke dokumen historis vs dokumen aktif terbaru. |
| Billing Lama Dibuka Langsung | Jika billing lama dibuka langsung dari route dokumen dan sudah punya pembetulan yang lebih baru, sistem menampilkan banner resolusi terlebih dahulu. PDF historis tetap bisa dibuka eksplisit melalui jalur `historical=1`. |
| PDF Historis | Billing original yang sudah punya pembetulan diberi watermark/catatan `sudah dipembetulkan` agar file PDF yang dibuka langsung tetap terbaca sebagai dokumen historis. |
| Label Aksi Dokumen | Label/tip aksi billing sekarang dinamis: billing aktif tetap `Cetak Billing` dan `Unduh Billing`, billing original yang sudah punya pembetulan berubah menjadi `Lihat Resolusi Dokumen` dan `Unduh Billing Historis`, sedangkan billing pembetulan terbaru tampil sebagai `Cetak Billing Pembetulan` dan `Unduh Billing Pembetulan`. |

### 10.2 SPTPD (Surat Pemberitahuan Pajak Terutang Daerah)

| Aspek | Detail |
|-------|--------|
| Nama | SPTPD |
| Template | `documents.sptpd` |
| Kondisi Terbit | Billing berstatus `paid` dan `sptpd_number` diterbitkan setelah syarat `isTriwulanComplete()` terpenuhi |
| Penomoran | `sptpd_number` = `billing_code` (auto-set saat status → paid) |
| Konten | Data lengkap billing + konfirmasi pelunasan, termasuk tanggal bayar dari `paid_at` |
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

### 10.5a SKRD Sewa Tanah (Surat Ketetapan Retribusi Daerah — Sewa Tanah)

| Aspek | Detail |
|-------|--------|
| Nama | SKRD Sewa Tanah |
| Template | `documents.skrd-sewa-tanah` |
| Format Nomor | `SKRD/{YYYY}/{MM}/{000001}` |
| Konten | Data wajib bayar, objek retribusi, jenis retribusi, tarif nominal, durasi, jumlah retribusi, masa berlaku, jatuh tempo, tanda tangan elektronik, QR code |
| Perhitungan | `Jumlah Retribusi = Tarif Nominal × Durasi` (tarif tetap, bukan persentase) |
| Kode Billing | Menggunakan prefix `41104` via `billing_kode_override` pada jenis pajak `42101` |
| Akses | Backoffice (`admin`, `verifikator`, `petugas`) |
| Route | `/skrd-sewa/{skrdId}/view` (cetak), `/skrd-sewa/{skrdId}/download` (unduh PDF) |

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
| SKRD Sewa Tanah | Draft oleh petugas, terbit oleh verifikator; billing memakai kode prefix 41104 | Pimpinan | PDF F4 |
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
| `expired` | Lewat Jatuh Tempo | gray | ✅ |
| `rejected` | Ditolak | danger | ❌ |
| `cancelled` | Dibatalkan | gray | ❌ |

**Status aktif** (blocking duplikat): `pending`, `paid`, `verified`, `expired`, `partially_paid`

**Sinkronisasi jatuh tempo:** Jika `payment_expired_at` sudah lewat dan billing masih berstatus `pending`, `verified`, atau `partially_paid`, sistem menyinkronkan status tersimpan menjadi `expired` dengan label tampil `Lewat Jatuh Tempo`. Billing `expired` tetap diperlakukan sebagai kewajiban aktif untuk pembayaran manual, pembuatan STPD manual, pembetulan, dashboard tagihan, dan blocking duplikat sampai ada proses bisnis lain yang menutupnya.

**Scheduler:** Command `tax:sync-expired-statuses` dijalankan terjadwal setiap jam agar sinkronisasi status overdue tidak menunggu halaman portal/backoffice diakses.

**Ringkasan notifikasi operator:** Saat scheduler mengubah billing ke `expired`, sistem mengirim notifikasi backoffice berisi total billing terdampak, daftar kode billing dalam batch, dan ringkasan jumlah per jenis pajak agar operator bisa membaca dampak perubahan otomatis tanpa membuka log terlebih dahulu.

**Batas aman notifikasi batch besar:** Jika batch auto-expire sangat besar, daftar kode billing dan ringkasan jenis pajak dipotong otomatis menjadi sampel terdepan dengan penanda `+N lainnya` agar body notifikasi tetap ringkas dan stabil di panel operator.

**Histori auto-expire backoffice:** Halaman `Activity Log` di backoffice menyediakan shortcut `Histori Auto-Expire` dan filter `Riwayat Otomatis = Auto-Expire Billing` untuk melihat jejak sinkronisasi billing lewat jatuh tempo tanpa query manual.

**Filter tanggal cepat:** Operator dapat memfilter `Activity Log` dengan opsi cepat `Hari ini`, `7 hari terakhir`, dan `30 hari terakhir` untuk membaca batch auto-expire terbaru lebih cepat tanpa mengisi rentang tanggal manual.

**Deep-link notifikasi backoffice:** Notifikasi sinkronisasi billing lewat jatuh tempo di bell icon Filament menyertakan tombol `Lihat Histori Auto-Expire` yang langsung membuka halaman histori auto-expire backoffice.

**Prioritas histori auto-expire:** Halaman `Histori Auto-Expire` menonjolkan batch dengan `Jumlah Billing` terbesar terlebih dahulu agar operator langsung melihat batch paling berdampak.

**Ringkasan status asal:** Body notifikasi scheduler dan tabel histori auto-expire menampilkan komposisi status asal billing sebelum disinkronkan, misalnya `Menunggu Pembayaran`, `Terverifikasi`, atau `Dibayar Sebagian`, sehingga operator bisa membedakan sumber batch auto-expire dengan cepat.

**Filter status asal:** `Activity Log` menyediakan filter cepat `Status Asal` untuk melihat hanya batch auto-expire yang berasal dari `Menunggu Pembayaran`, `Terverifikasi`, atau `Dibayar Sebagian`.

**Navigasi histori auto-expire:** Riwayat auto-expire diakses dari area `Activity Log` backoffice melalui shortcut `Histori Auto-Expire`, sehingga tidak ditampilkan lagi sebagai card terpisah di dashboard.

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

Jika pembetulan pertama yang salah dibatalkan, billing pengganti berikutnya tetap ditampilkan sebagai `Pembetulan ke-1`, tetapi sistem menyimpan attempt internal baru agar tidak terjadi duplicate data pada histori billing yang sudah dibatalkan.

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
- Khusus reklame, sub jenis pajak dipakai sebagai kategori operasional/umbrella pada objek pajak dan flow SKPD, yaitu `REKLAME_TETAP` untuk reklame tetap dan `REKLAME_KAIN` sebagai umbrella reklame insidentil

### 12.3 Harga Patokan Reklame
- **CRUD** detail jenis reklame: kode detail, nama, sub jenis induk reklame, flag insidentil, urutan, status aktif
- **Akses backoffice:** Admin only
- Menjadi source of truth detail reklame `RKL_*` untuk kalkulator, tarif reklame, pembuatan SKPD, dan permohonan sewa reklame
- Masing-masing detail reklame memiliki child tariff temporal pada menu yang sama
- **Input desimal fleksibel:** Di child tariff temporal, field `NSPR`, `NJOPR`, dan `Tarif Pokok` menerima input desimal dengan titik (`.`) maupun koma (`,`), lalu dinormalisasi sebelum validasi dan simpan.

### 12.4 Pimpinan (Penandatangan)
- **CRUD** data pimpinan: kabupaten, OPD, jabatan, bidang, sub-bidang, nama, pangkat, NIP
- **Akses backoffice:** Admin only
- Digunakan untuk tanda tangan digital pada SKPD dan STPD
- Verifikator di-assign ke satu Pimpinan

### 12.5 Harga Patokan MBLB
- **CRUD** harga patokan mineral: nama mineral, nama alternatif (JSON), harga patokan, satuan, dasar hukum, berlaku mulai/sampai
- **Akses backoffice:** Admin only
- Temporal — berlaku untuk periode tertentu
- **Input numerik backoffice:** field harga patokan menerima titik atau koma untuk desimal; nilai dinormalisasi saat form disimpan

### 12.6 Harga Patokan Sarang Walet
- **CRUD** harga patokan sarang: nama jenis, harga, satuan, dasar hukum, berlaku mulai/sampai
- **Akses backoffice:** Admin only
- **Input numerik backoffice:** field harga menerima titik atau koma untuk desimal; nilai dinormalisasi saat form disimpan

### 12.7 Harga Satuan Listrik
- **CRUD** harga satuan listrik per wilayah: nama wilayah, harga per kWh, dasar hukum, berlaku mulai/sampai
- **Akses backoffice:** Admin only
- Digunakan untuk perhitungan PPJ Non-PLN
- **Input numerik backoffice:** field harga per kWh menerima titik atau koma untuk desimal; nilai dinormalisasi saat form disimpan

### 12.8 NPA Air Tanah
- **CRUD** NPA: kelompok pemakaian (1–5), kriteria SDA (1–4), NPA per m³, NPA tiers (bertingkat), berlaku mulai/sampai, dasar hukum
- **Akses backoffice:** Admin only
- Tier lookup: untuk setiap volume bracket, NPA yang berbeda bisa diterapkan
- **Input numerik backoffice:** field volume tier dan NPA menerima titik atau koma untuk desimal; nilai dinormalisasi saat form disimpan

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

### 12.12 Instansi / Satker
- **CRUD** master instansi terkait billing: kode, nama, kategori, alamat/lokasi, asal wilayah, provinsi, kabupaten/kota, kecamatan, kelurahan/desa, keterangan, status aktif
- **Pemilihan searchable:** saat membuat billing self-assessment OPD dan billing MBLB `WAPU` di backoffice, field instansi mendukung pencarian nama/kategori langsung dari form billing
- **Akses backoffice:** Admin only
- **Seed awal:** aplikasi menyediakan seed bawaan `InstansiSeeder` berisi 3344 data satker awal yang disalin dari referensi `docs/ref_satker.csv`
- **Kategori seed awal:** data awal dibagi otomatis dari `docs/ref_satker.csv` menjadi `OPD` (1-70, 3173-3212, 3351), `Pemerintah Desa/Pemdes` (71-506), dan `Lembaga` (507-3172, 3213-3350); informasi desa/kelurahan serta kecamatan sumber dipetakan ke kolom wilayah terstruktur yang memakai referensi wilayah yang sama dengan asal wilayah wajib pajak

---

## 13. Fitur Reklame (Pajak Iklan)

### 13.1 Objek Reklame
- Terdaftar di tabel `tax_objects` dengan scope jenis pajak `41104`
- Atribut: NIK, NPWPD, NOPD, nama reklame, alamat, kelurahan, kecamatan, kelompok lokasi, bentuk, dimensi (panjang/lebar/tinggi/dll), luas otomatis, jumlah muka, tarif, foto, GPS, tanggal pasang, masa berlaku
- Objek reklame menyimpan sub jenis pajak operasional reklame, bukan detail tarif `RKL_*`
- Status objek reklame otomatis berubah dari `aktif` ke `kadaluarsa` ketika `masa_berlaku_sampai` lewat, sehingga daftar objek aktif portal/mobile tidak menampilkan izin yang sudah habis masa berlakunya
- Detail objek portal dan detail SKPD portal menampilkan histori visual foto objek lama-vs-baru dari `activity_logs`, lengkap dengan preview file historis yang aman untuk pemilik data maupun petugas backoffice

### 13.2 Tarif Reklame
- Per detail harga patokan reklame + kelompok lokasi + satuan waktu
- Temporal (berlaku mulai/sampai)
- Komponen: NSPR, NJOPR, tarif pokok (= (NSPR+NJOPR) × 25%)
- Satuan waktu: perTahun, perBulan, perMinggu, perHari, perLembar, perMingguPerBuah, perHariPerBuah
- Source of truth detail reklame berada pada master **Harga Patokan Reklame**, sedangkan `Sub Jenis Pajak` reklame tetap dipakai sebagai kategori induk (`REKLAME_TETAP` untuk reklame tetap dan `REKLAME_KAIN` untuk reklame insidentil)

### 13.3 Nilai Strategis Reklame
- Surcharge tambahan berdasarkan kelas kelompok (A/B/C) dan range luas
- Hanya untuk reklame tetap, luas ≥ 10m², satuan perTahun/perBulan
- Tarif per tahun dan per bulan terpisah
- Jika master nilai strategis tidak memiliki `berlaku_mulai`, baris tersebut tetap dianggap aktif untuk preview dan draft SKPD
- Panel preview perhitungan dan tombol aksi di sisi kanan bergerak sebagai satu blok pada layar besar agar tidak saling menimpa saat halaman di-scroll ke bawah

### 13.4 Perpanjangan Reklame
- WP dapat mengajukan perpanjangan via portal web atau mobile app
- Durasi: 30, 90, 180, atau 365 hari
- Syarat: izin reklame ≤ 30 hari dari kadaluarsa atau sudah kadaluarsa
- Status: diajukan → menunggu verifikasi → diproses → disetujui/ditolak
- **Akses backoffice:** Pengajuan reklame dari portal WP/mobile ditangani pada menu terpisah **Pengajuan Reklame Portal** untuk admin/petugas

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
- Field angka desimal di form backoffice menerima input `.` maupun `,`, lalu dinormalisasi saat validasi/simpan; `luas_m2` tetap dihitung otomatis dari panjang × lebar dan disimpan mengikuti presisi model
- **Akses backoffice:** Admin, verifikator, dan petugas dapat list/view; create, edit, delete, restore, dan force delete hanya untuk admin
- **Aksi operasional:** `set maintenance` dan `pinjam OPD` tersedia untuk admin, verifikator, dan petugas; `set tersedia` dan `selesai pinjam` hanya untuk admin

**Status Ketersediaan:**

| Status | Deskripsi | Auto-Update |
|--------|-----------|-------------|
| `tersedia` | Belum disewa | ✅ |
| `disewa` | Sedang disewa | ✅ |
| `maintenance` | Sedang perbaikan | ❌ (manual) |
| `tidak_aktif` | Tidak aktif | ❌ (manual) |
| `dipinjam_opd` | Dipinjam OPD | ✅ (otomatis setelah masa pinjam lewat) |

**Observer:** Perubahan status SKPD yang terkait aset Pemkab memicu sinkronisasi status aset berdasarkan ada/tidaknya SKPD `disetujui` yang masih aktif; status manual `maintenance` dan `tidak_aktif` tidak ditimpa. Khusus `dipinjam_opd`, sistem akan otomatis menutup riwayat pinjam aktif dan mengembalikan aset ke `tersedia` ketika `pinjam_selesai` sudah lewat.

### 13.7 Peminjaman Aset Reklame (OPD)
- Fitur terpisah dari sewa komersial
- Untuk instansi pemerintah (OPD) meminjam aset reklame
- Data: OPD peminjam, materi pinjam, durasi, bukti dukung
- Status: `aktif` → `selesai`

### 13.8 Permohonan Sewa Reklame
- **Publik (tanpa login):** Ajukan sewa aset reklame Pemkab langsung dari website
- **Upload:** KTP dan desain reklame wajib; NPWP opsional
- **Akses backoffice:** List/detail permohonan hanya untuk petugas; verifikator bekerja pada tahap verifikasi SKPD yang dihasilkan, bukan pada resource permohonan
- **Pemisahan menu:** Resource ini khusus untuk permohonan sewa reklame aset milik Pemkab dan terpisah dari menu **Pengajuan Reklame Portal** untuk pengajuan reklame WP/mobile
- **Aksi backoffice petugas:** `proses`, `tolak`, `cek NPWPD`, `buat NPWPD`, `perlu revisi`, dan `buat SKPD` sesuai status permohonan
- **Fallback email akun WP:** Saat `buat NPWPD`, petugas boleh mengosongkan email dan sistem akan membuat email login otomatis yang terbaca dari nama, alamat, nomor telepon, dan suffix acak
- **Penanda username di UI:** Notifikasi sukses `buat NPWPD` menampilkan label berbeda antara `Username login otomatis` dan `Email login WP` agar petugas tidak salah menyampaikan username
- **Audit revisi materi reklame:** Saat pemohon mengunggah ulang `file_desain_reklame` pada status `perlu_revisi`, sistem menyimpan versi file lama dan file baru ke `activity_logs` dengan preview historis. Riwayat materi ini tampil di detail objek reklame dan detail SKPD terkait
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
- **Pemrosesan decimal fleksibel:** Action `Proses SKPD` menerima input desimal dengan titik (`.`) maupun koma (`,`), lalu menormalisasi nilai meter dan tarif sebelum hitung `usage`, dasar pengenaan, dan draft SKPD.

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

## 14a. Fitur Retribusi Sewa Tanah

### 14a.1 Deskripsi
Retribusi Sewa Tanah adalah retribusi daerah atas pemakaian tanah milik pemerintah untuk kegiatan tertentu. Dokumen ketetapannya berupa **SKRD (Surat Ketetapan Retribusi Daerah)**, berbeda dengan SKPD untuk pajak daerah.

### 14a.2 Sub Jenis & Tarif

| Kode | Nama | Tarif Nominal | Satuan |
|------|------|---------------|--------|
| `SEWA_TANAH_PERMANEN` | Pemakaian Tanah untuk Pemasangan Reklame Permanen | Rp 80.000 | per Tahun |
| `SEWA_TANAH_KAIN` | Pemakaian Tanah untuk Pemasangan Kain Reklame/Umbul-umbul | Rp 20.000 | per Bulan |
| `SEWA_TANAH_RUMIJA` | Pemakaian Tanah untuk Ruang Udara diatas RUMIJA | Rp 80.000 | per Tahun |

- Tarif disimpan di tabel `tarif_sewa_tanah` dan bersifat temporal (berlaku mulai/sampai)
- Retribusi sewa tanah tetap berdiri sebagai master retribusi tersendiri, bukan memakai master pajak reklame
- `SEWA_TANAH_KAIN` adalah sub jenis retribusi sewa tanah yang dipakai untuk penempatan reklame insidentil
- **Rumus Perhitungan:** `Jumlah Retribusi = Luas m² × Jumlah Reklame × Rate Sub Jenis × Durasi`
- Luas m² diambil dari objek retribusi (yang terhubung ke objek reklame di tabel `tax_objects`)
- Nilai `rate` yang disimpan pada `tarif_sewa_tanah` adalah nominal final yang dipakai langsung saat menghitung SKRD
- Pairing sub jenis pada pendaftaran objek retribusi mengikuti kategori objek reklame:
  - `REKLAME_TETAP` hanya dapat dipasangkan dengan `SEWA_TANAH_PERMANEN` atau `SEWA_TANAH_RUMIJA`
  - `REKLAME_KAIN` sebagai kategori reklame insidentil hanya dapat dipasangkan dengan `SEWA_TANAH_KAIN`

### 14a.3 Objek Retribusi Sewa Tanah

| Aspek | Detail |
|-------|--------|
| Model | `App\Domain\Retribusi\Models\ObjekRetribusiSewaTanah` |
| Tabel | `objek_retribusi_sewa_tanah` |
| NOP | Auto-increment per NPWPD |
| FK | `tax_object_id` → `tax_objects` (objek reklame) |
| Filament | `ObjekRetribusiSewaTanahResource` (navigasi: Pendaftaran, sort 3) |
| Alur Form | Pilih NPWPD → pilih objek reklame milik NPWPD → tampil info wajib pajak → pilih sub jenis → isi data objek retribusi |
| Akses | admin, petugas |
| Lokasi Objek Retribusi | `kecamatan` dan `kelurahan` dipilih dari master wilayah Kabupaten Bojonegoro |
| Data Terenkripsi | NIK, nama pemilik, alamat pemilik, nama objek, alamat objek |

### 14a.4 SKRD Sewa Tanah

| Aspek | Detail |
|-------|--------|
| Model | `App\Domain\Retribusi\Models\SkrdSewaRetribusi` |
| Tabel | `skrd_sewa_retribusi` |
| Format Nomor | `SKRD/{YYYY}/{MM}/{000001}` |
| Workflow | Draft oleh petugas → Verifikator setujui/tolak |
| Halaman Input | `BuatSkrdSewaTanah` (navigasi: Laporan Petugas) |
| Sub Jenis pada Draft SKRD | Read-only di halaman buat SKRD dan selalu diwariskan dari `ObjekRetribusiSewaTanah` yang dipilih |
| Informasi Tarif di Halaman Input | Menampilkan tarif aktif, satuan masa sub jenis, masa tarif aktif, dan menghitung otomatis `masa berlaku sampai` dari tanggal mulai + durasi sesuai satuan waktu tarif |
| Halaman Verifikasi | `SkrdSewaRetribusiResource` (navigasi: Verifikasi, sort 4, badge count draft) |
| On Approve | Generate nomor SKRD resmi, kode billing (prefix 41104 via `billing_kode_override`), jatuh tempo, record Tax |
| Kode Billing | Menggunakan `JenisPajak::getBillingKode()` yang mengembalikan `41104` (bukan `42101`) untuk kompatibilitas sistem billing lama |
| Dokumen PDF | Template `documents.skrd-sewa-tanah`, judul "SURAT KETETAPAN RETRIBUSI DAERAH (SKRD)" |
| Route | `/skrd-sewa/{skrdId}/view` (cetak), `/skrd-sewa/{skrdId}/download` (unduh PDF) |
| Controller | `SkrdSewaDocumentController` |
| Akses PDF | Role backoffice (`admin`, `verifikator`, `petugas`) |
| Self-verify Prevention | Petugas pembuat SKRD tidak dapat memverifikasi SKRD yang dibuatnya sendiri |
| Bulk Actions | Bulk approve dan bulk reject untuk verifikasi massal |
| Data Terenkripsi | NIK, nama, alamat wajib bayar, nama objek, alamat objek, tarif nominal, jumlah retribusi |
| Overlap Guard | Tidak bisa membuat SKRD draft untuk NIK + sub jenis + periode yang tumpang tindih |

### 14a.5 Billing Kode Override

Karena kode jenis pajak `42101` baru dan unik, tetapi sistem billing lama memerlukan prefix `41104`, field `billing_kode_override` pada tabel `jenis_pajak` digunakan untuk memetakan:

```
JenisPajak(kode=42101, billing_kode_override=41104)
  → getBillingKode() returns '41104'
  → Tax::generateBillingCode('41104') → prefix '3522104...'
```

Jenis pajak lain yang tidak punya override tetap menggunakan kode aslinya.

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
- Notifikasi portal yang memiliki target halaman menyimpan URL relatif/absolut pada `app_notifications.data_payload.url`; dropdown notifikasi portal akan menandai notif sebagai terbaca lalu mengarahkan user ke URL tersebut saat item diklik.
- Notifikasi backoffice yang memiliki target halaman memakai action tombol `Lihat` pada payload database notification Filament.
- Command best-effort `php artisan notifications:backfill-urls` tersedia untuk mengisi `data_payload.url` pada notifikasi portal lama berdasarkan pola judul yang dikenali.

### 16.2 Event yang Memicu Notifikasi

#### 16.2.1 Notifikasi ke Wajib Pajak (App Notification)
Disimpan di tabel `app_notifications`, tampil di portal & mobile app WP.

| Modul | Event | Trigger |
|-------|-------|---------|
| Akun WP | Verifikasi akun disetujui / ditolak | Verifikator memproses pendaftaran wajib pajak |
| SKPD PBJT | SKPD/billing dibuat | Verifikator menerbitkan SKPD PBJT |
| SKPD Air Tanah | SKPD Air Tanah disetujui / ditolak | Verifikator memproses draft SKPD Air Tanah |
| SKPD Reklame | SKPD Reklame disetujui / ditolak | Verifikator memproses draft SKPD Reklame |
| STPD | STPD terbit | Sistem/verifikator menerbitkan STPD untuk pajak terutang |
| Perubahan Data | Perubahan data disetujui / ditolak | Verifikator memproses request perubahan data WP / objek pajak |
| Pembetulan | Pembetulan diproses | Verifikator menerima permohonan pembetulan |
| Pembetulan | Pembetulan selesai (billing pengganti dibuat) | Verifikator menyelesaikan pembetulan |
| Pembetulan | Pembetulan ditolak | Verifikator menolak permohonan pembetulan |
| Permohonan Sewa Reklame | Permohonan sewa diproses (disetujui / ditolak / billing dibuat) | Verifikator memproses permohonan sewa reklame |
| Gebyar Pajak | Gebyar submission diverifikasi (disetujui / ditolak) | Admin/verifikator memproses submission gebyar |
| Self Assessment MBLB | Submission MBLB diproses | Verifikator memproses self assessment MBLB |
| Laporan Meter Air Tanah | Laporan meter diproses | Verifikator/petugas memvalidasi laporan meter |
| Pembayaran | Pembayaran manual dikonfirmasi | Verifikator mengonfirmasi pembayaran manual |
| Status Pajak | Status pajak otomatis berubah | Cron `SyncExpiredTaxStatuses` mendeteksi masa berlaku habis; backoffice menerima 1 notifikasi terpisah per jenis pajak yang lewat jatuh tempo |

#### 16.2.2 Notifikasi ke Backoffice (Filament Notification)
Disimpan di tabel `notifications` (database notification Laravel), tampil di bell icon panel admin.

| Modul | Event | Penerima |
|-------|-------|----------|
| STPD | Draft STPD baru menunggu verifikasi | Verifikator |
| SKPD Air Tanah | Draft SKPD Air Tanah baru menunggu verifikasi | Verifikator |
| SKPD Reklame | Draft SKPD Reklame baru menunggu verifikasi | Verifikator |
| Pembetulan | Permohonan pembetulan baru dari WP | Verifikator |
| Self Assessment | Self assessment MBLB baru dari WP | Verifikator |
| Reklame Request | Permohonan reklame baru dari WP | Verifikator |
| Permohonan Sewa Reklame | Permohonan sewa baru dari WP | Petugas / Verifikator |
| Gebyar Pajak | Gebyar submission baru dari WP | Verifikator |
| Portal MBLB | Submission MBLB baru dari portal | Verifikator |
| Laporan Meter | Meter report dikirim WP | Petugas |
| Perubahan Data | Permintaan perubahan data WP / objek pajak baru diajukan | Admin & Verifikator |
| Status Pajak | Pajak lewat jatuh tempo terdeteksi cron | Role terkait (admin/verifikator/petugas), 1 notifikasi terpisah per jenis pajak, action menuju histori auto-expire |

### 16.3 Broadcast
- `NotificationService::broadcast()` — kirim notifikasi massal ke seluruh user dengan role `wajibPajak` (mis. pengumuman dari admin).

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
- **Standar password aplikasi:** minimal 7 karakter, wajib mengandung minimal 1 huruf kapital, 1 huruf kecil, 1 angka, dan 1 tanda baca/karakter non-alphabetic; checklist standar ini ditampilkan pada form ubah password portal, form password user di backoffice, serta modal reset password backoffice, lalu divalidasi konsisten pada flow registrasi API, ubah password web/API, create/edit user backoffice, dan reset password user backoffice
- **Single session lintas kanal:** satu akun hanya boleh memiliki satu sesi aktif pada satu waktu; login baru dari portal, backoffice admin, atau mobile API akan merotasi `active_session_id`, menyimpan metadata kanal/waktu/device/IP sesi aktif, menampilkan notifikasi bahwa sesi sebelumnya digantikan, lalu mengakhiri akses sesi lama pada request berikutnya dengan pesan yang menjelaskan dari kanal dan perangkat mana login baru terjadi; token API lama ikut menjadi stale dan ditolak dengan respons informatif ketika dipakai kembali
- **Pemisahan sesi portal vs backoffice di browser:** browser yang sama dapat login sebagai wajib pajak di portal dan sekaligus login sebagai `admin`/`verifikator`/`petugas` di backoffice pada tab berbeda karena guard sesi web dipisah. Logout portal tidak lagi mematikan sesi backoffice, dan logout backoffice tidak lagi mematikan sesi portal. Namun seluruh role backoffice tetap memakai guard yang sama, sehingga admin dan petugas tidak bisa dipertahankan bersamaan dalam browser profile yang sama.
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
- **Input angka desimal:** field `rating`, `latitude`, dan `longitude` menerima input dengan titik (`.`) maupun koma (`,`), lalu dinormalisasi otomatis sebelum validasi dan penyimpanan
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
