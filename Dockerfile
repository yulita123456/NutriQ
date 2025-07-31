# Gunakan image dasar PHP yang sudah terintegrasi dengan Apache, cocok untuk Laravel
# Anda bisa sesuaikan versi PHP (misal: php:8.3-apache). Pastikan sesuai dengan versi PHP lokal Anda.
FROM php:8.2-apache

# Instal dependensi sistem yang dibutuhkan, termasuk Tesseract OCR dan data bahasanya
# 'tesseract-ocr' adalah paket utama Tesseract
# 'tesseract-ocr-ind' adalah paket data bahasa Indonesia untuk Tesseract (sering sudah termasuk tessdata_best/fast)
# '--no-install-recommends' untuk menjaga ukuran image tetap kecil
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpng-dev \
        libjpeg-dev \
        libwebp-dev \
        tesseract-ocr \
        tesseract-ocr-ind \
        # Opsional: Jika Anda menggunakan ekstensi GD untuk manipulasi gambar di PHP
        libonig-dev \
        libzip-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libwebp-dev && \
    rm -rf /var/lib/apt/lists/*

# Instal ekstensi PHP yang dibutuhkan oleh Laravel dan library OCR Anda
# 'proc_open' yang dibutuhkan thiagoalessio/tesseract-ocr-for-php biasanya sudah aktif
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# Aktifkan module rewrite Apache (penting untuk URL cantik Laravel)
RUN a2enmod rewrite

# Atur document root Apache ke direktori 'public' Laravel
# Ini memastikan web server melayani dari direktori public aplikasi Anda
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf

# Salin kode aplikasi Laravel Anda ke dalam container
COPY . /var/www/html

# Atur direktori kerja ke root aplikasi Laravel
WORKDIR /var/www/html

# Instal Composer untuk mengelola dependensi PHP
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Instal dependensi Composer (tanpa dev dependencies untuk produksi, optimasi autoloader)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Atur izin yang sesuai untuk direktori 'storage' dan 'bootstrap/cache' Laravel
# User Apache default adalah 'www-data'
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Opsional: Jika Anda ingin memastikan menggunakan tessdata_best terbaru dari GitHub
# Paket 'tesseract-ocr-ind' seharusnya sudah menginstal versi yang bagus (seringkali fast/best).
# Hanya lakukan ini jika Anda yakin paket default tidak cukup akurat.
# RUN wget -O /usr/share/tesseract-ocr/tessdata/ind.traineddata https://github.com/tesseract-ocr/tessdata_best/raw/main/ind.traineddata

# Ekspos port 80 (default untuk server web Apache)
EXPOSE 80

# Perintah default untuk menjalankan server Apache di foreground
CMD ["apache2-foreground"]
