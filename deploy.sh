#!/bin/bash

set -e

cd ~/rebalax

cp .env.example .env

[ -n "$APP_KEY" ] && sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" .env
[ -n "$APP_ENV" ] && sed -i "s|^APP_ENV=.*|APP_ENV=$APP_ENV|" .env
[ -n "$APP_URL" ] && sed -i "s|^APP_URL=.*|APP_URL=$APP_URL|" .env
[ -n "$APP_DEBUG" ] && sed -i "s|^APP_DEBUG=.*|APP_DEBUG=$APP_DEBUG|" .env
[ -n "$DB_DATABASE" ] && sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|" .env
[ -n "$DB_USERNAME" ] && sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USERNAME|" .env
[ -n "$DB_PASSWORD" ] && sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" .env
[ -n "$REDIS_PASSWORD" ] && sed -i "s|^REDIS_PASSWORD=.*|REDIS_PASSWORD=$REDIS_PASSWORD|" .env
[ -n "$QUEUE_CONNECTION" ] && sed -i "s|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=$QUEUE_CONNECTION|" .env
[ -n "$GRAFANA_ADMIN_USER" ] && sed -i "s|^GRAFANA_ADMIN_USER=.*|GRAFANA_ADMIN_USER=$GRAFANA_ADMIN_USER|" .env
[ -n "$GRAFANA_ADMIN_PASSWORD" ] && sed -i "s|^GRAFANA_ADMIN_PASSWORD=.*|GRAFANA_ADMIN_PASSWORD=$GRAFANA_ADMIN_PASSWORD|" .env
[ -n "$GRAFANA_DB_USERNAME" ] && sed -i "s|^GRAFANA_DB_USERNAME=.*|GRAFANA_DB_USERNAME=$GRAFANA_DB_USERNAME|" .env
[ -n "$GRAFANA_DB_PASSWORD" ] && sed -i "s|^GRAFANA_DB_PASSWORD=.*|GRAFANA_DB_PASSWORD=$GRAFANA_DB_PASSWORD|" .env
[ -n "$GRAFANA_DB_DATABASE" ] && sed -i "s|^GRAFANA_DB_DATABASE=.*|GRAFANA_DB_DATABASE=$GRAFANA_DB_DATABASE|" .env

cp ~/docker/grafana/provisioning/datasources/datasource.yml.example ~/docker/grafana/provisioning/datasources/datasource.yml

sed -i "s|\$env.GRAFANA_DB_USERNAME|${GRAFANA_DB_USERNAME}|g" ~/docker/grafana/provisioning/datasources/datasource.yml
sed -i "s|\$env.GRAFANA_DB_PASSWORD|${GRAFANA_DB_PASSWORD}|g" ~/docker/grafana/provisioning/datasources/datasource.yml
sed -i "s|\$env.GRAFANA_DB_DATABASE|${GRAFANA_DB_DATABASE}|g" ~/docker/grafana/provisioning/datasources/datasource.yml

docker-compose up -d --build

docker exec rebalax-app composer install --no-dev

docker exec rebalax-app php artisan config:clear
docker exec rebalax-app php artisan migrate --force
docker exec rebalax-app php artisan cache:clear
docker exec rebalax-app php artisan route:clear
docker exec rebalax-app php artisan view:clear
docker exec rebalax-app php artisan optimize
