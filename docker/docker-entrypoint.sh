#!/bin/sh

# Pastikan symlink storage terbuat atau sudah ada
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link
fi

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
