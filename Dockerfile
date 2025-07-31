# Use a PHP base image
FROM php:8.2-apache

# Install system dependencies, Tesseract, and Node.js
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
    # Install Node.js and NPM
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# Configure Apache with a custom vhost file and enable mod_rewrite
COPY docker/apache_config.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Copy the Laravel application code into the container
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Install Composer and run all build commands
# We group these commands to ensure they run in the correct context
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install --no-dev --optimize-autoloader --no-interaction && \
    npm install && \
    npm run build && \
    php artisan migrate --force && \
    php artisan storage:link

# Crucial for permissions: run this after storage:link
# First, change the ownership of the entire directory to the www-data user
RUN chown -R www-data:www-data /var/www/html && \
    # Then, set more granular permissions on specific directories
    chmod -R 775 storage bootstrap/cache

# Run Laravel caching commands after permissions are set
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Expose port 80 and start Apache
EXPOSE 80
CMD ["apache2-foreground"]
