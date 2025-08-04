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
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd pdo pgsql pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

USER root

RUN chown -R www-data:www-data /var/www
RUN git config --global --add safe.directory /var/www

RUN DD_APM_INSTRUMENTATION_LIBRARIES=php:1 \
    DD_APM_INSTRUMENTATION_ENABLED=docker \
    DD_NO_AGENT_INSTALL=true \
    bash -c "$(curl -L https://install.datadoghq.com/scripts/install_script_agent7.sh)"

COPY docker/crontab/laravel /etc/cron.d/laravel
RUN chmod 0644 /etc/cron.d/laravel
RUN crontab /etc/cron.d/laravel
