---
description: How to setup and run Borotax Admin Panel
---

# Setup Borotax Admin Panel

## Prerequisites
1. PHP 8.1+ installed
2. Composer installed
3. MySQL/MariaDB running
4. Node.js & NPM installed

## Steps

### 1. Start MySQL/MariaDB Server
Make sure your Laragon is started or MySQL service is running.

### 2. Run Database Migrations
// turbo
```bash
php artisan migrate:fresh --seed
```
This will create all tables and seed initial data including:
- Admin user (admin@bapenda.bojonegorokab.go.id / admin123)
- Verifikator user (verifikator@bapenda.bojonegorokab.go.id / verifikator123)
- Petugas user (petugas@bapenda.bojonegorokab.go.id / petugas123)
- Jenis Pajak data (Hotel, Restoran, Hiburan, Reklame, etc)
- Sub Jenis Pajak data
- App Version data for mobile app

### 3. Install NPM Dependencies (if not done)
// turbo
```bash
npm install
```

### 4. Build Assets
// turbo
```bash
npm run build
```

### 5. Start Development Server
// turbo
```bash
php artisan serve
```

### 6. Access Admin Panel
Open browser and go to: http://localhost:8000/admin

Login with:
- Email: admin@bapenda.bojonegorokab.go.id
- Password: admin123

## Filament Resources Available
- **Master Data**
  - Jenis Pajak - CRUD untuk jenis pajak daerah
  - Sub Jenis Pajak - CRUD untuk sub jenis pajak
  - User Management - Kelola pengguna sistem
- **Verifikasi**
  - Wajib Pajak - Verifikasi pendaftaran wajib pajak
- **CMS**
  - Berita - Kelola berita dari Bapenda
  - Destinasi - Kelola destinasi wisata/kuliner
- **Sistem**
  - Activity Log - Lihat log aktivitas sistem

## Dashboard Widgets
- Stats Overview (Total WP, Pendapatan, Pengajuan Menunggu, SKPD Terbit)
- Pengajuan Terbaru (Tabel pendaftaran yang menunggu verifikasi)
