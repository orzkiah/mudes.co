FROM php:8.4-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
        postgresql-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql bcmath zip intl opcache mbstring gd exif \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Get Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set up PHP configurations
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/zz-memory-limit.ini \
    && echo "upload_max_filesize=100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=100M" >> /usr/local/etc/php/conf.d/uploads.ini

# Set permissions for storage & bootstrap/cache
RUN chmod -R 777 storage bootstrap/cache

# Expose port (Koyeb and Render will set the PORT env variable, defaulting to 8000)
EXPOSE 8000

# Start script using the dynamic PORT env variable provided by host
CMD php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
