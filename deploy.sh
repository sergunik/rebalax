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

docker-compose -f docker-compose.prod.yml build
docker-compose -f docker-compose.prod.yml up -d

sleep 10
curl -f http://localhost:8000 > /dev/null

docker exec rebalax-app php artisan config:clear
docker exec rebalax-app php artisan cache:clear
docker exec rebalax-app php artisan route:clear
docker exec rebalax-app php artisan view:clear
docker exec rebalax-app php artisan migrate --force

ln -sfn "$RELEASE_DIR" "$CURRENT"

cd "$DEPLOY_BASE/releases"
ls -dt release_* | tail -n +6 | while read dir; do
    cd "$DEPLOY_BASE/releases/$dir"
    docker-compose -f docker-compose.prod.yml down -v
    rm -rf "$DEPLOY_BASE/releases/$dir"
done

echo "Deployment completed: $TIMESTAMP"
