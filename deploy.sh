#!/bin/bash

set -e

cd ~/rebalax

cp .env.example .env

[ -n "$APP_KEY" ] && sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" .env
[ -n "$APP_ENV" ] && sed -i "s|^APP_ENV=.*|APP_ENV=$APP_ENV|" .env
[ -n "$APP_URL" ] && sed -i "s|^APP_URL=.*|APP_URL=$APP_URL|" .env
[ -n "$DB_DATABASE" ] && sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|" .env
[ -n "$DB_USERNAME" ] && sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USERNAME|" .env
[ -n "$DB_PASSWORD" ] && sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" .env
[ -n "$REDIS_PASSWORD" ] && sed -i "s|^REDIS_PASSWORD=.*|REDIS_PASSWORD=$REDIS_PASSWORD|" .env
[ -n "$QUEUE_CONNECTION" ] && sed -i "s|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=$QUEUE_CONNECTION|" .env

docker-compose -f docker-compose.prod.yml up -d --build

docker exec rebalax-app composer install --no-dev --optimize-autoloader

docker exec rebalax-app php artisan config:clear
docker exec rebalax-app php artisan cache:clear
docker exec rebalax-app php artisan route:clear
docker exec rebalax-app php artisan view:clear
docker exec rebalax-app php artisan migrate --force
docker exec rebalax-app php artisan optimize
