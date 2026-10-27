FROM richarvey/nginx-php-fpm:latest

ENV WEBROOT /var/www/html/public

WORKDIR /var/www/html

COPY . /var/www/html

RUN composer install --no-dev --optimize-autoloader

# Run migrations BEFORE caching config/routes
RUN php artisan migrate --force

RUN php artisan config:cache
RUN php artisan route:cache