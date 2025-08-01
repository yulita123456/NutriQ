# Use a PHP base image
FROM php:8.2-apache

# Install system dependencies, Tesseract, dan Node.js
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
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# Atur konfigurasi Apache
COPY docker/apache_config.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Salin kode aplikasi
COPY . /var/www/html

# Atur direktori kerja
WORKDIR /var/www/html

# --- TAMBAHAN KRUSIAL UNTUK BUILD LARAVEL ---
# Set environment variables untuk fase build. Ini akan mencegah error konfigurasi.
ENV APP_ENV=production
ENV APP_DEBUG=false

# Instal Composer dan NPM dependencies dan build frontend
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install --no-dev --optimize-autoloader --no-interaction && \
    npm install && \
    npm run build

# Atur izin direktori
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 storage bootstrap/cache public/build

# Atur skrip entrypoint
COPY docker/docker-entrypoint.sh /var/www/html/docker/docker-entrypoint.sh
RUN chmod +x /var/www/html/docker/docker-entrypoint.sh

# Ekspos port 80 dan jalankan skrip startup
EXPOSE 80
CMD ["/bin/sh", "/var/www/html/docker/docker-entrypoint.sh"]
