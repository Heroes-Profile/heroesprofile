FROM php:8.1-fpm

WORKDIR /var/www/html

COPY . .

RUN apt-get update && \
    apt-get install -y libpng-dev && \
    docker-php-ext-install pdo pdo_mysql gd

CMD ["php-fpm"]
