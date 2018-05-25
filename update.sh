#!/usr/bin/env bash

php7 artisan down

composer install

php7 artisan migrate --force

php7 artisan cache:clear
php7 artisan config:cache
php7 artisan view:cache
php7 artisan route:cache

php7 artisan up