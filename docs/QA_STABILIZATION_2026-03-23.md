# QA Stabilization Summary - 2026-03-23

## Status

- Feature suite terakhir yang divalidasi: `vendor/bin/phpunit --bootstrap vendor/autoload.php tests/Feature`
- Hasil validasi terakhir: `OK (149 tests, 1314 assertions)`
- Fokus sesi ini: pemulihan regresi workflow Filament, sinkronisasi policy/resource, dan penyelarasan dokumentasi fitur terhadap implementasi aktual

## Runtime Fixes

### 1. TaxResource copy/export

- Serialisasi status billing dibuat aman untuk enum-backed value saat copy dan export laporan
- Aksi copy menggunakan `navigator.clipboard.writeText()` sebagai jalur utama
- Fallback tetap tersedia melalui textarea sementara jika clipboard API gagal
- Notifikasi sukses ditangani dari backend Filament, bukan mengandalkan global JavaScript notification

### 1.1 Object ownership and extension guards

- `WaterTaxController::submitReport()` sekarang menolak pengiriman laporan meter untuk objek air tanah yang bukan milik user login
- `ReklameController::submitExtension()` sekarang menyamakan guard API dengan aturan portal: objek harus milik user login, belum punya pengajuan aktif, dan hanya boleh diajukan saat objek kedaluwarsa atau sisa masa berlaku maksimal 30 hari
- `Web\ReklameController::requestExtension()` dan `storeExtension()` sekarang menegakkan eligibility yang sama pada jalur portal, bukan hanya di level tampilan
- `ReklameObject` dan `WaterObject` tidak lagi meng-overwrite `nik_hash` di event `saving`, sehingga hash kepemilikan tetap konsisten dengan nilai plaintext yang dihasilkan trait enkripsi
- Regression test baru: `tests/Feature/MobileApiGuardTest.php`

## Latest Validation

- Full feature suite sesudah fix guard API dan hash kepemilikan: `OK (149 tests, 1314 assertions)`
- Final focused regression bundle sesudah hardening dokumen, STPD, policy-resource sync, dan helper seed reklame: `OK (88 tests, 613 assertions)`
- Verifikasi ulang direct phpunit untuk kasus yang sebelumnya sempat flaky: `php vendor/bin/phpunit tests/Feature/TaxStpdWorkflowTest.php --stop-on-error --testdox` → `OK (8 tests, 79 assertions)`
- Focused validation tambahan yang dijalankan:
  - `tests/Feature/MobileApiGuardTest.php` → `OK (3 tests, 11 assertions)`
  - `tests/Feature/ReklameWorkflowTest.php tests/Feature/SkpdAirTanahVerificationWorkflowTest.php` → `OK (12 tests, 150 assertions)`
  - `tests/Feature/TaxStpdWorkflowTest.php` → `OK (8 tests, 79 assertions)`
  - `tests/Feature/SkpdDocumentAccessTest.php` → `OK (6 tests, 15 assertions)`
  - `tests/Feature/StpdManualDocumentAccessTest.php` → `OK (2 tests, 4 assertions)`
  - `tests/Feature/MasterDataModuleTest.php` → `OK (3 tests, 22 assertions)`
  - `tests/Feature/AuthorizationConsistencyResourceTest.php` → `OK (30 tests, 130 assertions)`
  - `tests/Feature/AuthorizationConsistencyResourceTest.php --filter=admin_only_resources_keep_navigation_and_access_in_sync_with_policies` → `OK (18 tests, 54 assertions)`
  - `tests/Feature/TaxConfigurationResourceActionTest.php` → `OK (12 tests, 122 assertions)`
  - `tests/Feature/TaxResourceHeaderActionTest.php` → `OK (9 tests, 63 assertions)`
  - `tests/Feature/DataChangeRequestWorkflowTest.php tests/Feature/ReklameWorkflowTest.php` → `OK (12 tests, 146 assertions)`
  - `tests/Feature/PendaftaranModuleTest.php` → `OK (3 tests, 21 assertions)`

## Test Fixes

### 2. Shared reklame tax seeding guardrail

- Setup referensi pajak reklame untuk test yang membutuhkan kode `RKL_*` dipusatkan ke `Tests\TestCase::seedReklameTaxReferences()` agar dependency `ReklameSubJenisPajakSeeder` tetap eksplisit dan tidak tersebar sebagai array seeder manual.
- Validasi ulang sesudah refactor: `tests/Feature/ReklameWorkflowTest.php` → `OK (8 tests, 78 assertions)` dan `tests/Feature/MobileApiGuardTest.php` → `OK (3 tests, 11 assertions)`.

### 3. TaxResource header action regression

- Ekspektasi test diperbarui agar memeriksa perilaku clipboard yang aktual
- File terkait: `tests/Feature/TaxResourceHeaderActionTest.php`

### 3. Filament edit page route key handling

- Workflow `DataChangeRequest` diperbaiki dengan mengirim route key alih-alih model instance pada parameter `record`
- Ini mencegah `ModelNotFoundException` pada page test berbasis UUID / Filament edit page
- File terkait: `tests/Feature/DataChangeRequestWorkflowTest.php`

## Access and Workflow Alignment

### 4. Wajib Pajak verification and edit flow

- Hak edit data WP yang sudah disetujui diselaraskan dengan flow `DataChangeRequest`
- Akses review/verifikasi dipastikan konsisten dengan kombinasi policy, resource, dan workflow test

### 5. SKPD verification flows

- Dokumentasi dan verifikasi akses dibedakan jelas antara:
  - halaman pembuatan draft oleh petugas/admin
  - halaman verifikasi oleh admin/verifikator
- Berlaku untuk SKPD Reklame dan SKPD Air Tanah

### 6. Pembetulan workflow

- Review pembetulan didokumentasikan sebagai proses backoffice oleh admin/verifikator
- Hasil akhir berupa billing pengganti atau penolakan

## Documentation Alignment

### 7. docs/FITUR_APLIKASI.md diselaraskan dengan implementasi aktual

Rujukan utama:

- Latar umum fitur: [FITUR_APLIKASI.md](./FITUR_APLIKASI.md)
- Kontrak API mobile dan guard terbaru: [FITUR_APLIKASI.md#8-fitur-aplikasi-mobile-api](./FITUR_APLIKASI.md#8-fitur-aplikasi-mobile-api)
- Dokumen output dan batas akses PDF: [FITUR_APLIKASI.md#10-dokumen-yang-dihasilkan](./FITUR_APLIKASI.md#10-dokumen-yang-dihasilkan)
- Keamanan, Simpadu, CMS, audit trail, dan versioning: [FITUR_APLIKASI.md#17-keamanan--enkripsi](./FITUR_APLIKASI.md#17-keamanan--enkripsi), [FITUR_APLIKASI.md#18-integrasi-sistem-lama-simpadu](./FITUR_APLIKASI.md#18-integrasi-sistem-lama-simpadu), [FITUR_APLIKASI.md#19-fitur-cms-content-management](./FITUR_APLIKASI.md#19-fitur-cms-content-management), [FITUR_APLIKASI.md#21-sistem-audit-trail](./FITUR_APLIKASI.md#21-sistem-audit-trail), [FITUR_APLIKASI.md#22-versi-aplikasi-mobile](./FITUR_APLIKASI.md#22-versi-aplikasi-mobile)

Area yang disinkronkan:

- Dashboard backoffice custom
- Klarifikasi ringkasan role admin di bagian awal dokumen
- Flow duplicate billing self-assessment dan akses Pembatalan Billing
- Klarifikasi penerbitan SPTPD/STPD pada flow lunas bayar manual
- Penyesuaian trigger STPD otomatis agar menunggu `isTriwulanComplete()` seperti SPTPD untuk mode otomatis
- Penambahan regression test untuk memastikan route portal SPTPD/STPD tetap `404` sebelum triwulan lengkap dan tersedia sesudahnya
- Penambahan guard akses dokumen billing/SPTPD/STPD agar portal user hanya bisa membuka billing miliknya sendiri, sementara role backoffice tetap diizinkan
- Penambahan guard akses dokumen SKPD Reklame/SKPD Air Tanah agar route autentik hanya bisa dibuka pemilik objek/SKPD atau role backoffice
- Klarifikasi validasi duplikasi STPD manual (`draft` / `disetujui`)
- Klarifikasi aksi dokumen TaxResource dan availability guard SPTPD/STPD di portal
- Klarifikasi tabel ringkasan dokumen untuk trigger SPTPD/STPD
- Harmonisasi wording NPWP opsional pada sewa reklame publik/mobile
- Hak akses verifikasi dan review
- TaxResource copy/export behavior
- Wajib Pajak approved edit vs change request flow
- Aset Reklame Pemkab, Permohonan Sewa Reklame, signed URL SKPD sewa, dan Laporan Meter
- Kontrak API mobile: OTP, auth, self-assessment, air tanah, reklame, sewa reklame, gebyar, notifikasi, dan envelope response
- Master data admin-only sections
- Activity Log sebagai audit trail immutable
- Notifikasi `notifyUser()`, `notifyRole()`, dan `notifyUserBoth()`
- Portal/public route inventory
- Batasan portal vs mobile untuk objek Air Tanah dan Reklame
- Dokumen output, signed URL, dan batas akses PDF

## Important Verified Notes

### Portal and public routes

- Portal memiliki route notifikasi operasional untuk list, unread count, mark single, dan mark all
- Route self-assessment portal mencakup index, form per jenis pajak, dan success page
- Dokumen autentikasi di luar prefix `/portal` tetap dipakai untuk SKPD Reklame, SKPD Air Tanah, dan STPD manual

### Portal feature boundaries

- Portal web Air Tanah dipakai untuk monitoring objek dan SKPD, bukan untuk registrasi objek atau pelaporan meter
- Portal web Reklame dipakai untuk melihat objek yang sudah ada, mengajukan perpanjangan, dan memantau SKPD
- Registrasi objek baru diarahkan ke mobile app atau proses kantor sesuai modulnya

### Document access rules

- Route billing status mengarahkan ke SPTPD bila billing sudah lunas dan nomor SPTPD tersedia
- SPTPD view/download akan `404` jika nomor SPTPD belum tersedia
- Route PDF SKPD Reklame/Air Tanah pada jalur autentik mengembalikan `404` untuk portal user yang bukan pemilik objek/SKPD
- STPD manual PDF hanya tersedia setelah status `disetujui`, dan route autentik juga mengembalikan `404` untuk portal user yang bukan pemilik tax terkait
- Signed URL publik SKPD Reklame hanya berlaku untuk permohonan sewa yang disetujui; unsigned URL ditolak oleh middleware `signed`
- Route lampiran `/permohonan-sewa/{id}/file/{field}` sekarang dibatasi ke pemilik permohonan atau role backoffice; field di luar whitelist tetap `404`
- SKPD Air Tanah tidak memiliki signed URL publik

### Mobile API contracts

- OTP registrasi memakai expiry 30 detik, cooldown 2 menit, dan batas 3 request per 15 menit
- Login API memakai field `email`, tetapi menerima email maupun NIK
- Login API saat ini memeriksa status akun terkunci yang sudah ada, tetapi mekanisme increment lockout 5 kali gagal masih ditegakkan penuh pada portal web
- Semua response API v1 memakai envelope `success`, `data`, `message`
- Endpoint notifikasi mobile dipaginasi 20 item per halaman
- Billing check publik memakai query parameter `code`
- Laporan meter air tanah dan pengajuan perpanjangan reklame kini tervalidasi terhadap kepemilikan objek user login

## Follow-up Candidates

1. Tambahkan referensi silang dari dokumen QA ini ke changelog internal bila tim menyimpan catatan release terpisah.
2. Pertimbangkan memecah inventaris endpoint/model dari `docs/FITUR_APLIKASI.md` ke dokumen teknis khusus bila lampiran makin panjang.