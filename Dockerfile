# Gunakan image dasar PHP yang sudah terintegrasi dengan Apache, cocok untuk Laravel
# Anda bisa sesuaikan versi PHP (misal: php:8.3-apache). Pastikan sesuai dengan versi PHP lokal Anda.
FROM php:8.2-apache

# Instal dependensi sistem yang dibutuhkan, termasuk Tesseract OCR dan data bahasanya
# 'tesseract-ocr' adalah paket utama Tesseract
# 'tesseract-ocr-ind' adalah paket data bahasa Indonesia untuk Tesseract
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpng-dev \
        libjpeg-dev \
        libwebp-dev \
        tesseract-ocr \
        tesseract-ocr-ind \
        # Opsional: Ekstensi GD untuk manipulasi gambar di PHP
        libonig-dev \
        libzip-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libwebp-dev && \
    rm -rf /var/lib/apt/lists/*

# Instal ekstensi PHP yang dibutuhkan oleh Laravel dan library OCR Anda
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# Aktifkan module rewrite Apache (penting untuk URL cantik Laravel)
RUN a2enmod rewrite

# Atur document root Apache ke direktori 'public' Laravel
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf

# Salin kode aplikasi Laravel Anda ke dalam container
COPY . /var/www/html

# Set direktori kerja ke root aplikasi Laravel
WORKDIR /var/www/html

# --- Start: Menjalankan semua perintah build dari custom command Anda ---

# Instal Composer untuk mengelola dependensi PHP (jika belum ada)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Jalankan Composer install untuk dependensi PHP (sesuai custom command Anda)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Instal NPM dependencies (sesuai custom command Anda)
RUN npm install

# Build aset frontend (Vite) untuk produksi (sesuai custom command Anda)
RUN npm run build

# Jalankan migrasi database (sesuai custom command Anda)
# --force digunakan karena ini dijalankan dalam mode non-interaktif (saat build Docker)
RUN php artisan migrate --force

# Buat symbolic link untuk storage (sesuai custom command Anda)
RUN php artisan storage:link

# Bersihkan dan buat cache konfigurasi, rute, dan view untuk performa produksi (sesuai custom command Anda)
# Perintah 'clear' untuk memastikan tidak ada cache lama yang bermasalah, lalu 'cache' untuk optimasi
RUN php artisan config:clear
RUN php artisan route:clear
RUN php artisan view:clear
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# --- End: Menjalankan semua perintah build dari custom command Anda ---

# Atur izin yang sesuai untuk direktori 'storage', 'bootstrap/cache', dan 'public/build' Laravel
# Izin perlu diatur *setelah* semua file/direktori dibuat atau dimodifikasi
RUN chown -R www-data:www-data storage bootstrap/cache public/build
RUN chmod -R 775 storage bootstrap/cache public/build

# Ekspos port 80 (default untuk server web Apache)
EXPOSE 80

# Perintah default untuk menjalankan server Apache di foreground
CMD ["apache2-foreground"]
