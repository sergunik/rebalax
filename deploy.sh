#!/bin/bash

set -e

TIMESTAMP=$(date +%Y%m%d%H%M%S)
DEPLOY_BASE=~/rebalax
RELEASE_DIR=$DEPLOY_BASE/releases/release_$TIMESTAMP
CURRENT=$DEPLOY_BASE/current

mkdir -p "$RELEASE_DIR"
cp -R ~/rebalax-temp/* "$RELEASE_DIR"
rm -rf ~/rebalax-temp

cd "$RELEASE_DIR"

cp .env.example .env

[ -n "$APP_KEY" ] && sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" .env
[ -n "$APP_ENV" ] && sed -i "s|^APP_ENV=.*|APP_ENV=$APP_ENV|" .env
[ -n "$APP_URL" ] && sed -i "s|^APP_URL=.*|APP_URL=$APP_URL|" .env
[ -n "$DB_DATABASE" ] && sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|" .env
[ -n "$DB_USERNAME" ] && sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USERNAME|" .env
[ -n "$DB_PASSWORD" ] && sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" .env
[ -n "$REDIS_PASSWORD" ] && sed -i "s|^REDIS_PASSWORD=.*|REDIS_PASSWORD=$REDIS_PASSWORD|" .env
[ -n "$QUEUE_CONNECTION" ] && sed -i "s|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=$QUEUE_CONNECTION|" .env

docker-compose -f docker-compose.prod.yml build
docker-compose -f docker-compose.prod.yml up -d

sleep 10
curl -f http://localhost:8000 > /dev/null

docker exec rebalax-app composer install --no-dev --optimize-autoloader

docker exec rebalax-app php artisan config:clear
docker exec rebalax-app php artisan cache:clear
docker exec rebalax-app php artisan route:clear
docker exec rebalax-app php artisan view:clear
docker exec rebalax-app php artisan migrate --force
docker exec rebalax-app php artisan optimize

ln -sfn "$RELEASE_DIR" "$CURRENT"

cd "$DEPLOY_BASE/releases"
ls -dt release_* | tail -n +6 | while read dir; do
    cd "$DEPLOY_BASE/releases/$dir"
    docker-compose -f docker-compose.prod.yml down -v
    rm -rf "$DEPLOY_BASE/releases/$dir"
done

echo "Deployment completed: $TIMESTAMP"
