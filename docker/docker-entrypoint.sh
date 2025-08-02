#!/bin/sh
set -e

echo "--- Memulai docker-entrypoint.sh (METODE PAKSA) ---"

# Langkah 1: Hapus paksa link lama untuk menghindari konflik
echo "Menghapus symlink lama di public/storage (jika ada)..."
rm -rf /var/www/html/public/storage

# Langkah 2: Pastikan izin folder tujuan sudah benar
echo "Memastikan izin folder storage..."
chown -R www-data:www-data /var/www/html/storage

# Langkah 3: BUAT SYMLINK SECARA MANUAL DAN PAKSA
echo "Membuat symlink secara manual dari /storage/app/public ke /public/storage..."
ln -s /var/www/html/storage/app/public /var/www/html/public/storage
echo "Perintah ln -s selesai."

# Langkah 4: Jalankan migrasi dan cache
echo "Menjalankan migrasi dan caching..."
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "--- Selesai setup, menjalankan Apache ---"
exec apache2-foreground
