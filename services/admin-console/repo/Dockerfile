FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure intl \
    && docker-php-ext-install intl zip pdo_pgsql

# Set working directory
WORKDIR /var/www

# Ensure permissions (optional but good practice)
RUN chown -R www-data:www-data /var/www
