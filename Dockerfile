FROM richarvey/nginx-php-fpm:latest

ENV WEBROOT /var/www/html/public

WORKDIR /var/www/html

COPY . /var/www/html

RUN composer install --no-dev --optimize-autoloader
RUN rm -f bootstrap/cache/config.php

# Ensure your start script is executable
RUN chmod +x /var/www/html/start.sh

# Override the base image's entry point to run your script first, then the base image's original command
ENTRYPOINT ["/bin/sh", "-c", "/var/www/html/start.sh"]
