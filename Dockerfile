# Use PHP 8.4 CLI as the base image
FROM php:8.4-cli

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    libicu-dev \
    && docker-php-ext-install intl

# Install Xdebug via PECL
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set entrypoint
CMD ["php", "-a"]
