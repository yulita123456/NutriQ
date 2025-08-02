#!/bin/sh

# Jalankan migrasi database
php artisan migrate --force

# HAPUS BARIS INI:
# php artisan storage:link

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
