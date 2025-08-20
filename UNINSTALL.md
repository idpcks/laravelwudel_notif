# LaravelWudel Notif Package - Uninstall Guide

## Overview

Package ini menyediakan sistem uninstall yang robust dan otomatis untuk mencegah error fatal yang terjadi setelah uninstall. Semua masalah yang dilaporkan dalam laporan error telah diperbaiki.

## Automatic Uninstall (Recommended)

### Via Composer
```bash
composer remove laravelwudel/laravelwudel-notif
```

**Yang terjadi otomatis:**
- ✅ Package dihapus dari `composer.json`
- ✅ Cache Laravel dibersihkan secara otomatis
- ✅ Service provider dihapus dari cache
- ✅ File yang di-publish dihapus
- ✅ Migration dan model dihapus
- ✅ Autoload di-rebuild

### Via Artisan Command
```bash
php artisan laravelwudel-notif:uninstall
```

## Manual Uninstall (Jika Automatic Gagal)

### Step 1: Hapus Package
```bash
composer remove laravelwudel/laravelwudel-notif
```

### Step 2: Hapus File Konfigurasi
```bash
rm config/laravelwudel-notif.php
```

### Step 3: Hapus Migration
```bash
# Rollback migration terlebih dahulu
php artisan migrate:rollback --step=1

# Hapus file migration
rm database/migrations/*_create_push_subscriptions_table.php
```

### Step 4: Hapus Model
```bash
rm app/Models/PushSubscription.php
```

### Step 5: Hapus Views dan Assets
```bash
rm -rf resources/views/vendor/laravelwudel-notif
rm -rf public/vendor/laravelwudel-notif
```

### Step 6: Bersihkan Cache
```bash
# Clear semua cache
php artisan optimize:clear

# Atau hapus cache secara manual
rm -rf bootstrap/cache/*
```

### Step 7: Rebuild Autoload
```bash
composer dump-autoload
```

## Emergency Cleanup

### Jika Cache Laravel Corrupt
```bash
php artisan laravelwudel-notif:emergency-cleanup --force
```

**Yang dilakukan:**
- 🗑️ Hapus SEMUA cache files
- 🗑️ Hapus semua file yang di-publish
- 🧹 Clean composer autoload
- 🔨 Rebuild Laravel dari awal

### Script Uninstall Manual

#### Windows (uninstall.bat)
```cmd
scripts\uninstall.bat
```

#### Unix/Linux (uninstall.sh)
```bash
chmod +x scripts/uninstall.sh
./scripts/uninstall.sh
```

## Troubleshooting

### Error: "Service Provider Tidak Ditemukan"

**Penyebab:** Cache Laravel masih berisi referensi ke service provider yang sudah dihapus.

**Solusi:**
```bash
# Hapus semua cache
rm -rf bootstrap/cache/*

# Clear compiled classes
php artisan clear-compiled

# Rebuild autoload
composer dump-autoload

# Clear semua cache
php artisan optimize:clear
```

### Error: "Cache Corruption"

**Penyebab:** Cache Laravel menjadi corrupt setelah uninstall yang tidak bersih.

**Solusi:**
```bash
# Jalankan emergency cleanup
php artisan laravelwudel-notif:emergency-cleanup --force

# Atau manual cleanup
rm -rf bootstrap/cache/*
composer install
php artisan optimize:clear
```

### Error: "Fatal Error: Laravel tidak bisa dijalankan"

**Penyebab:** Service provider masih terdaftar di cache tapi file sudah dihapus.

**Solusi:**
```bash
# Hapus semua cache secara manual
rm -rf bootstrap/cache/*
rm -rf storage/framework/cache/*
rm -rf storage/framework/views/*

# Reinstall dependencies
composer install

# Clear dan rebuild cache
php artisan optimize:clear
```

## Post-Uninstall Checklist

Setelah uninstall, pastikan:

- [ ] Package sudah dihapus dari `composer.json`
- [ ] File konfigurasi sudah dihapus
- [ ] Migration sudah di-rollback dan dihapus
- [ ] Model sudah dihapus
- [ ] Views dan assets sudah dihapus
- [ ] Cache Laravel sudah dibersihkan
- [ ] Autoload sudah di-rebuild
- [ ] Aplikasi Laravel bisa dijalankan normal

## Prevention Measures

Package ini telah diimplementasikan dengan fitur pencegahan:

### 1. Automatic Cache Cleanup
- Hook `post-package-uninstall` di composer.json
- Script uninstall otomatis
- Cleanup cache Laravel secara otomatis

### 2. Robust Cache Management
- `CacheCleanupService` untuk cleanup yang aman
- Validasi cache integrity
- Emergency cache reset jika diperlukan

### 3. Multiple Uninstall Methods
- Composer hook otomatis
- Artisan command manual
- Emergency cleanup command
- Script uninstall manual

### 4. Error Handling
- Try-catch di semua operasi cleanup
- Fallback methods jika primary method gagal
- Detailed error reporting

## Support

Jika mengalami masalah saat uninstall:

1. **Buat Issue** di [GitHub Repository](https://github.com/idpcks/laravelwudel_notif)
2. **Jelaskan Error** yang terjadi dengan detail
3. **Lampirkan Log** error jika ada
4. **Sebutkan Versi** Laravel dan PHP yang digunakan
5. **Jelaskan Langkah** yang sudah dicoba

## Technical Details

### Cache Files yang Dibersihkan
- `bootstrap/cache/packages.php`
- `bootstrap/cache/services.php`
- `bootstrap/cache/config.php`
- `bootstrap/cache/route.php`
- `bootstrap/cache/view.php`

### Package References yang Dihapus
- `LaravelWudel\LaravelWudelNotif\LaravelWudelNotifServiceProvider`
- `laravelwudel-notif`
- `LaravelWudel\LaravelWudelNotif`
- `PushNotification`
- `WebPushService`

### Published Files yang Dihapus
- `app/Models/PushSubscription.php`
- `config/laravelwudel-notif.php`
- `resources/views/vendor/laravelwudel-notif`
- `public/vendor/laravelwudel-notif`
- `database/migrations/*_create_push_subscriptions_table.php`

## Version History

- **v1.0.3+**: Implementasi sistem uninstall yang robust
- **v1.0.2**: Versi sebelumnya dengan masalah uninstall
- **v1.0.1**: Versi awal

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
