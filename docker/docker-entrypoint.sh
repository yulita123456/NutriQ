#!/bin/sh

if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link
fi

php artisan migrate --force
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
