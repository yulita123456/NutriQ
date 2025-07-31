# Gunakan image PHP base
FROM php:8.2-apache

# Instal dependensi sistem, termasuk Tesseract OCR dan paket bahasa Indonesia
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpng-dev \
        libjpeg-dev \
        libwebp-dev \
        tesseract-ocr \
        tesseract-ocr-ind \
        libonig-dev \
        libzip-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libwebp-dev && \
    rm -rf /var/lib/apt/lists/*

# Instal ekstensi PHP yang dibutuhkan
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# Aktifkan module rewrite Apache
RUN a2enmod rewrite

# Atur document root Apache ke 'public'
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf

# Salin kode aplikasi ke container
COPY . /var/www/html

# Atur direktori kerja
WORKDIR /var/www/html

# Instal Node.js dan NPM untuk aset frontend
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

# Instal Composer dan jalankan semua perintah build
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install --no-dev --optimize-autoloader --no-interaction && \
    npm install && \
    npm run build && \
    php artisan migrate --force && \
    php artisan storage:link && \
    php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Atur izin direktori
RUN chown -R www-data:www-data storage bootstrap/cache public/build
RUN chmod -R 775 storage bootstrap/cache public/build

# Ekspos port 80 dan jalankan Apache
EXPOSE 80
CMD ["apache2-foreground"]
