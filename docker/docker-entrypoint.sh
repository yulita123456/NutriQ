#!/bin/sh

# Script akan berhenti jika ada error, tidak akan lanjut diam-diam
set -e

echo "--- Memulai docker-entrypoint.sh ---"

# Menjalankan chown dan menampilkan pesan konfirmasi
echo "Menjalankan chown -R www-data:www-data /var/www/html/storage..."
chown -R www-data:www-data /var/www/html/storage
echo "Perintah chown selesai."

# Cek status kepemilikan setelah chown untuk verifikasi
echo "Verifikasi kepemilikan folder /var/www/html/storage:"
ls -ld /var/www/html/storage
echo "Verifikasi kepemilikan folder /var/www/html/storage/app/public:"
ls -ld /var/www/html/storage/app/public

# Jalankan migrasi database
echo "Menjalankan migrasi..."
php artisan migrate --force

# Membersihkan dan membuat cache
echo "Membersihkan cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Membuat cache baru..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "--- Selesai setup, menjalankan Apache ---"
# Jalankan Apache sebagai proses utama
exec apache2-foreground
