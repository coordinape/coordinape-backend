FROM php:8.0-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
  build-essential \
  libgmp-dev \
  unzip \
  zip \
  git \
  curl \
  libpq-dev \
  libpng-dev \
  libjpeg62-turbo-dev \
  && docker-php-ext-configure gd --with-jpeg \
  && docker-php-ext-install -j$(nproc) gd


RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pcntl gmp pdo pdo_pgsql pgsql

RUN groupadd -g 1000 www
RUN useradd -u 1000 -g www -ms /bin/bash www

COPY --chown=www:www . /var/www
RUN chown www:www /var/www

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER www

RUN composer install

EXPOSE 9000
CMD ["./services/start.sh"]