#!/usr/bin/env bash

cd ${CONTAINER_SRC_PATH}

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

sudo chmod 777 -R storage
sudo chmod 777 -R bootstrap/cache
