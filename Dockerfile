# ─────────────────────────────────────────────────────
# Imagen base: PHP 8.3 con FPM (FastCGI Process Manager)
# FPM es lo que Nginx usa para ejecutar tu código PHP
# ─────────────────────────────────────────────────────
FROM php:8.4-fpm

# ─────────────────────────────────────────────────────
# 1. Dependencias del sistema operativo
#    - git: para composer
#    - unzip: para descomprimir paquetes
#    - libpq-dev: para la extensión de PostgreSQL
#    - libredis: para phpredis nativo (más rápido)
# ─────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ─────────────────────────────────────────────────────
# 2. Extensiones PHP que Laravel necesita
#    pdo_pgsql → conectarse a PostgreSQL
#    zip       → composer y uploads
#    mbstring  → manejo de strings UTF-8
#    exif      → metadatos de imágenes (evidencias)
#    pcntl     → control de procesos (queue workers)
#    bcmath    → matemáticas precisas (coordenadas GPS)
# ─────────────────────────────────────────────────────
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    zip \
    mbstring \
    exif \
    pcntl \
    bcmath \
    xml

# ─────────────────────────────────────────────────────
# 3. Redis nativo (mucho más rápido que predis/php)
# ─────────────────────────────────────────────────────
RUN pecl install redis \
    && docker-php-ext-enable redis

# ─────────────────────────────────────────────────────
# 4. Composer (gestor de paquetes PHP)
#    Lo copiamos desde su imagen oficial
# ─────────────────────────────────────────────────────
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# ─────────────────────────────────────────────────────
# 5. Directorio de trabajo
#    Aquí vivirá tu proyecto Laravel (montado como volumen)
# ─────────────────────────────────────────────────────
WORKDIR /var/www

# ─────────────────────────────────────────────────────
# 6. Permisos
#    www-data es el usuario que usa PHP-FPM
#    Necesita escribir en storage/ y bootstrap/cache/
# ─────────────────────────────────────────────────────
RUN chown -R www-data:www-data /var/www

# ─────────────────────────────────────────────────────
# 7. Exponer el puerto de PHP-FPM
#    Nginx se conecta a este puerto internamente
# ─────────────────────────────────────────────────────
EXPOSE 9000

CMD ["php-fpm"]
