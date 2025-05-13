FROM php:7.2-fpm-buster

# Встановлення базових залежностей
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git \
        curl \
        libpng-dev \
        libxml2-dev \
        zip \
        unzip \
        libzip-dev \
        libxslt1-dev \
    ; \
    rm -rf /var/lib/apt/lists/*; \
    docker-php-ext-configure zip --with-libzip; \
    docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        xsl

# Встановлення Composer
COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer

# Налаштування робочої директорії
WORKDIR /var/www/html

# Копіювання файлів проекту
COPY . /var/www/html

# Встановлення прав
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Встановлення залежностей composer
RUN composer install --no-interaction --no-plugins --no-scripts

EXPOSE 9000

CMD ["php-fpm"]
