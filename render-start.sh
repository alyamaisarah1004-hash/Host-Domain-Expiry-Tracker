#!/bin/bash
rm -f bootstrap/cache/config.php
php artisan config:cache
php artisan migrate --force
/start.sh
