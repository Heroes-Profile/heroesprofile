#!/bin/bash

# .env file

if test -f ".env"; then
    echo ".env already exists"
else
    echo "generating .env from .env.example"
    cp .env.example .env
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