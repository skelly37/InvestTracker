FROM php:8.2-fpm

WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

# Copy application
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && mkdir -p /var/www/html/storage /var/www/html/logs \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/storage /var/www/html/logs

EXPOSE 9000

USER www-data

CMD ["php-fpm"]