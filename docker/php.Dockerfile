FROM php:7.1.11-fpm

RUN apt-get update && apt-get install -y \
    git zlib1g-dev libpq-dev libxslt1-dev \
    && docker-php-ext-install zip pdo pdo_mysql xsl;

COPY ./composer.json /var/www/html/composer.json
COPY ./composer.phar /var/www/html/composer.phar

ONBUILD RUN php composer.phar install --no-dev --prefer-dist --no-autoloader --no-scripts
ONBUILD RUN php composer.phar dump-autoload