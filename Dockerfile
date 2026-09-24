FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql zip gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chmod -R 775 storage bootstrap/cache

CMD php artisan migrate --force && php artisan serve --host 0.0.0.0 --port ${PORT:-10000}