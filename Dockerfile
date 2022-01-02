FROM php:8.0-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
  build-essential \
  libgmp-dev \
  unzip \
  zip \
  git \
  curl \
  libpq-dev

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pcntl gmp pdo pdo_pgsql pgsql

COPY . /var/www
RUN chown -R www-data:www-data /var/www

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER www-data

RUN composer install

EXPOSE 9000
CMD ["./services/start.sh"]
