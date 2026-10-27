#!/bin/bash
php artisan config:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
/start.sh