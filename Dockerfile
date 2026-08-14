FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev \
    libonig-dev libxml2-dev nginx \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
    pdo pdo_pgsql pgsql zip mbstring exif pcntl bcmath xml

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . /var/www

RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

COPY nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["/bin/bash", "-c", "php artisan migrate --force && php artisan config:cache && php artisan route:cache && service nginx start && php-fpm -F"]