---
description: How to deploy Borotax to Windows Server using Laragon
---

# Deploy Borotax ke Windows Server dengan Laragon

## Prasyarat di Server

1. **Install Laragon Full** dari https://laragon.org/download/
2. **PHP 8.2+** (sudah termasuk di Laragon Full)
3. **MySQL 8.0+** (sudah termasuk di Laragon)
4. **Composer** (sudah termasuk di Laragon)
5. **Git** (opsional, untuk clone repo)

---

## Langkah-langkah Deploy

### 1. Persiapan Folder Project

```powershell
# Salin folder project ke Laragon www directory
# Default: C:\laragon\www\borotax
```

Atau clone dari Git:
```powershell
cd C:\laragon\www
git clone <repository-url> borotax
```

### 2. Install Dependencies

```powershell
cd C:\laragon\www\borotax
composer install --optimize-autoloader --no-dev
```

### 3. Konfigurasi Environment

```powershell
# Copy .env.example ke .env
copy .env.example .env

# Generate app key
php artisan key:generate
```

Edit file `.env` sesuai server:
```env
APP_NAME=Borotax
APP_ENV=production
APP_DEBUG=false
APP_URL=http://borotax.local

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=borotax
DB_USERNAME=root
DB_PASSWORD=

# Simpadu Database (jika diperlukan)
DB_SIMPADU_HOST=127.0.0.1
DB_SIMPADU_PORT=3306
DB_SIMPADU_DATABASE=simpadu
DB_SIMPADU_USERNAME=root
DB_SIMPADU_PASSWORD=
```

### 4. Setup Database

```powershell
# Buat database di HeidiSQL/phpMyAdmin
# Atau via command:
mysql -u root -e "CREATE DATABASE borotax CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Jalankan migration
php artisan migrate --force

# (Opsional) Seed data awal
php artisan db:seed --force
```

### 5. Optimize untuk Production

```powershell
# Cache config, routes, dan views
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize autoloader
composer dump-autoload --optimize

# Link storage
php artisan storage:link
```

### 6. Setup Virtual Host di Laragon

1. Klik kanan icon Laragon → **Quick app** → **Apache** → **sites-enabled**
2. Atau otomatis dengan klik: **Menu** → **Preferences** → **General** → centang **Auto virtual hosts**
3. Restart Laragon

Domain otomatis: `http://borotax.test` atau `http://borotax.local`

### 7. Konfigurasi Apache (Opsional - Custom Domain)

Buat file `C:\laragon\etc\apache2\sites-enabled\borotax.conf`:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/laragon/www/borotax/public"
    ServerName borotax.local
    ServerAlias www.borotax.local
    
    <Directory "C:/laragon/www/borotax/public">
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>
    
    ErrorLog "C:/laragon/logs/borotax-error.log"
    CustomLog "C:/laragon/logs/borotax-access.log" combined
</VirtualHost>
```

### 8. Edit Hosts File (untuk domain local)

Edit `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 borotax.local
127.0.0.1 www.borotax.local
```

### 9. File Permissions

```powershell
# Pastikan folder storage dan bootstrap/cache writable
icacls "C:\laragon\www\borotax\storage" /grant Everyone:F /T
icacls "C:\laragon\www\borotax\bootstrap\cache" /grant Everyone:F /T
```

### 10. Restart Laragon

Klik kanan Laragon → **Reload Apache**

---

## Verifikasi Deployment

1. Buka browser: `http://borotax.local`
2. Akses admin panel: `http://borotax.local/admin`
3. Pastikan tidak ada error di `storage/logs/laravel.log`

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| 500 Error | Cek `storage/logs/laravel.log` |
| Database error | Pastikan DB credentials benar di `.env` |
| Asset tidak muncul | Jalankan `php artisan storage:link` |
| Permission denied | Jalankan perintah icacls di atas |
| Page not found | Pastikan Apache mod_rewrite aktif |

---

## Update Aplikasi

```powershell
cd C:\laragon\www\borotax

# Pull latest code (jika pakai Git)
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev

# Jalankan migration
php artisan migrate --force

# Clear & rebuild cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
