#!/bin/sh
set -e

echo "--- Memulai docker-entrypoint.sh (STRATEGI BARU) ---"

# Langkah 1: Pastikan izin folder storage sudah benar SEBELUM membuat link
echo "Memastikan izin folder storage..."
chown -R www-data:www-data /var/www/html/storage
echo "Izin folder storage OK."

# Langkah 2: Hapus paksa symlink lama untuk menghindari konflik
echo "Menghapus symlink lama di public/storage (jika ada)..."
rm -rf /var/www/html/public/storage
echo "Symlink lama dihapus."

# Langkah 3: Buat symlink yang baru dan bersih
echo "Menjalankan php artisan storage:link..."
php artisan storage:link
echo "Perintah storage:link selesai."

# Langkah 4: Verifikasi hasil untuk kita lihat di log deploy
echo "Verifikasi symlink yang baru dibuat:"
ls -l /var/www/html/public/

# Langkah 5: Jalankan migrasi dan cache
echo "Menjalankan migrasi dan caching..."
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "--- Selesai setup, menjalankan Apache ---"
exec apache2-foreground
