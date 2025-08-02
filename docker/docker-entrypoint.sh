#!/bin/sh
set -e

# Pastikan folder storage (untuk log, cache) tetap bisa ditulis
chown -R www-data:www-data /var/www/html/storage

# Jalankan migrasi dan cache
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Jalankan Apache
exec apache2-foreground
