#!/bin/sh

# Script akan berhenti jika ada error. Ini praktik yang baik.
set -e

# (SANGAT PENTING) Mengubah kepemilikan folder storage agar bisa diisi oleh web server.
chown -R www-data:www-data /var/www/html/storage

# Jalankan migrasi database.
php artisan migrate --force

# Membersihkan dan membuat cache untuk produksi.
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Menjalankan Apache sebagai proses utama.
exec apache2-foreground
