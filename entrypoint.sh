#!/bin/bash

# .env file

if test -f ".env"; then
    echo ".env already exists"
else
    echo "generating .env from .env.example"
    cp .env.example .env

    # gets docker root pw from docker-compose.yml
    ROOT_PW=$(sed -nE 's/.*MYSQL_ROOT_PASSWORD: (.*)$/\1/p' docker-compose.yml)
    # changes existing DB_HOST in .env to match the docker-compose database service
    sed -r -i 's/READ_DB_HOST=.*/READ_DB_HOST=database/g' .env
    sed -r -i 's/WRITE_DB_HOST=.*/WRITE_DB_HOST=database/g' .env
    # changes DB_PASSWORD in .env to match root password from docker-compose
    sed -r -i 's/DB_PASSWORD=.*/DB_PASSWORD='"$ROOT_PW"'/g' .env
fi

# Run composer install

composer install

# Create databases if they don't already exist

php artisan db:create

# Run php artisan key:generate make sure the APP_KEY has this value in the .env file - TODO - make conditional

php artisan key:generate

# Run npm install

npm install

# Run php artisan migrate - TODO - make conditional

php artisan migrate

# Run composer dump-autoload

composer dump-autoload

# Run php artisan db:seed - TODO - make conditional

php artisan db:seed

# Serve app

php /var/www/html/artisan serve --host=0.0.0.0 --port=80
