#!/bin/sh

# TAMBAHKAN BARIS INI
# Mengubah kepemilikan folder storage ke www-data agar bisa diisi oleh web server
chown -R www-data:www-data /var/www/html/storage

# Jalankan migrasi database
php artisan migrate --force

# Hapus cache yang mungkin usang
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Jalankan cache baru
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Jalankan Apache
exec apache2-foreground
