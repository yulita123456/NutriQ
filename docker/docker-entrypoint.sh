#!/bin/sh

# Pastikan symlink storage terbuat atau sudah ada
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link
fi

# Jalankan migrasi database
php artisan migrate --force

# Jalankan Apache
exec apache2-foreground
