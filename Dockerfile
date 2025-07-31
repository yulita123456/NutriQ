# Gunakan image PHP base
FROM php:8.2-apache

# Instal dependensi sistem, Tesseract, dan Node.js
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
    # Instal Node.js dan NPM
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

# Instal ekstensi PHP
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# Atur document root Apache ke 'public' dengan file konfigurasi kustom
# Ini lebih andal dari pada perintah sed
COPY docker/apache_config.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Salin kode aplikasi ke container
COPY . /var/www/html

# Atur direktori kerja
WORKDIR /var/www/html

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
