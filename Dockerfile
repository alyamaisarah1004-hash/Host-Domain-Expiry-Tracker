FROM richarvey/nginx-php-fpm:latest

ENV WEBROOT /var/www/html/public

WORKDIR /var/www/html

COPY . /var/www/html

RUN composer install --no-dev --optimize-autoloader

# Run migrations at RUNTIME, not build time
CMD php artisan migrate --force && php artisan config:cache && php artisan route:cache && /start.sh