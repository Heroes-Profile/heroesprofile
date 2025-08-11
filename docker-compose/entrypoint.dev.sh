#!/bin/bash
set -e

# Fix permissions on mounted volumes
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Create directories if they don't exist
mkdir -p /var/www/storage/logs
mkdir -p /var/www/bootstrap/cache

# Install dependencies if they don't exist or if package files have changed
if [ ! -d "vendor" ] || [ "composer.json" -nt "vendor/autoload.php" ]; then
    echo "Installing PHP dependencies..."
    composer install --no-interaction --prefer-dist
fi

if [ ! -d "node_modules" ] || [ "package.json" -nt "node_modules/.package-lock.json" ]; then
    echo "Installing Node.js dependencies..."
    npm install
fi

# Generate app key if it doesn't exist
if [ ! -f ".env" ]; then
    echo "Copying .env.docker to .env..."
    cp ./docker-compose/.env.docker .env
fi

if grep -q "APP_KEY=$" .env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Execute the command passed to the container
exec "$@"
