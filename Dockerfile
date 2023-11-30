FROM php:8.1-apache

# Open port 8000
EXPOSE 8000

# Install system dependencies
RUN apt-get update && apt-get install -y git zip unzip
RUN curl -L https://deb.nodesource.com/nsolid_setup_deb.sh | bash -s -- 20 && apt-get install nodejs -y

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

# Install Vite
RUN npm install --global vite 

# Install Vue 3 project dependencies
RUN npm install

# Build the Vue 3 project
RUN npm run build

COPY os-conf /

RUN a2ensite 000-default && a2enmod rewrite

# Start Apache
CMD ["apache2-foreground"]
