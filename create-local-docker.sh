#!/bin/bash

# Copy the .env file from the example if it doesn't already exist
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Start our docker containers from the docker-compose.yaml
docker-compose up -d --build

# Run a few commands to setup the application
docker compose exec heroesprofile bash -c 'php --version && node --version && npm --version'
docker compose exec heroesprofile bash -c 'composer install'
docker compose exec heroesprofile bash -c 'php artisan key:generate'
docker compose exec heroesprofile bash -c 'npm install'
docker compose exec heroesprofile bash -c 'npm run build'

# TODO - Create tables according to the app specs because there is nothing for now

docker compose ps

echo "Application should be on http://127.0.0.1:8000"