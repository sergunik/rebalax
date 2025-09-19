FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    cron \
    supervisor \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd pdo pgsql pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY docker/php82/laravel /etc/cron.d/laravel
RUN chmod 0644 /etc/cron.d/laravel && crontab /etc/cron.d/laravel

COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

USER root

RUN git config --global --add safe.directory /var/www
RUN chown -R www-data:www-data /var/www
