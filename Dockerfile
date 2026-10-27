FROM richarvey/nginx-php-fpm:latest

ENV WEBROOT /var/www/html/public

WORKDIR /var/www/html

COPY . /var/www/html

RUN composer install --no-dev --optimize-autoloader
RUN rm -f bootstrap/cache/config.php
