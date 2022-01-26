#!/bin/sh

composer install --prefer-source --no-interaction --no-dev --optimize-autoloader

# Disable migrations because they will now happen in Hasura.
# php artisan migrate --force
php-fpm