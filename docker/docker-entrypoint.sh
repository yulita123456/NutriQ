#!/bin/sh
set -e

# Hapus symlink lama untuk menghindari konflik
rm -rf /var/www/html/public/storage

# Pastikan izin folder storage sudah benar
chown -R www-data:www-data /var/www/html/storage

# Buat symlink yang baru dan bersih
php artisan storage:link

# Jalankan migrasi dan cache
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Jalankan Apache
exec apache2-foreground
