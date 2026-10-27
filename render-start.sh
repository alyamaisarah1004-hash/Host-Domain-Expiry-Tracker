#!/bin/bash
echo "CUSTOM START.SH IS RUNNING" >> /var/www/html/storage/logs/start.log
rm -f bootstrap/cache/config.php
php artisan config:cache
php artisan migrate --force
/start.sh   # ← This line is suspicious; confirm whether /start.sh is the base image's startup script
