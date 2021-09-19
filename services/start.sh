#!/bin/sh

composer install --prefer-source --no-interaction --no-dev --optimize-autoloader

php artisan migrate --force
php-fpm