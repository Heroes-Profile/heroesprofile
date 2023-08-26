FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory
COPY . .

# Change ownership of storage and cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copy public folder to Apache directory
RUN cp -R public/* /var/www/html/

COPY public/.htaccess /var/www/html/.htaccess

# Install Laravel dependencies
RUN composer install

# Install Node.js and npm
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get update -y \
    && apt-get install -y nodejs \
    && node -v \
    && npm -v

# Install Vite
RUN npm install --global vite 

# Install Vue 3 project dependencies
RUN npm install

# Build the Vue 3 project
RUN npm run build

COPY heroesprofile-244413-a9642e3d8df5.json /app/heroesprofile-244413-a9642e3d8df5.json

RUN curl -o /usr/local/bin/cloud-sql-proxy https://storage.googleapis.com/cloud-sql-connectors/cloud-sql-proxy/v2.6.1/cloud-sql-proxy.linux.amd64
RUN chmod +x /usr/local/bin/cloud-sql-proxy


COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY ports.conf /etc/apache2/ports.conf
RUN a2ensite 000-default

# Open port 8000
EXPOSE 8000

RUN a2enmod rewrite


# Start Apache
CMD ["sh", "-c", "GOOGLE_APPLICATION_CREDENTIALS=/app/heroesprofile-244413-a9642e3d8df5.json cloud-sql-proxy heroesprofile-244413:us-east1:heroesprofile & apache2-foreground"]


