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

ENV DD_TRACE_ENABLED=true \
    DD_AGENT_HOST=dd-agent \
    DD_ENV=prod \
    DD_SERVICE=rebalax-app \
    DD_VERSION=1.0.0

RUN curl -sSL https://github.com/DataDog/dd-trace-php/releases/latest/download/datadog-setup.php -o /tmp/dd-setup.php \
 && php /tmp/dd-setup.php --php-bin=/usr/local/bin/php --php-fpm-bin=/usr/local/sbin/php-fpm --enable-profiling=0 --enable-telemetry=1

COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

USER root

RUN git config --global --add safe.directory /var/www
RUN chown -R www-data:www-data /var/www
