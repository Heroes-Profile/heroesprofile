FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Create node_modules directory and set permissions
RUN mkdir -p /var/www/node_modules && \
    chown -R www-data:www-data /var/www

# Copy Apache configuration files
COPY 000-defaultDev.conf /etc/apache2/sites-available/000-default.conf
COPY ports.conf /etc/apache2/ports.conf

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2ensite 000-default && a2enmod rewrite \
    && echo "LimitRequestFieldSize 8192" >> /etc/apache2/apache2.conf \
    && chown -R www-data:www-data /var/www/html

COPY --chown=www-data:www-data ./docker-compose/entrypoint.dev.sh /usr/local/bin/entrypoint.dev.sh
RUN chmod +x /usr/local/bin/entrypoint.dev.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.dev.sh"]
