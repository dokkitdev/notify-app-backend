FROM nginx:1.21-alpine

# Видалення стандартної конфігурації nginx
RUN rm /etc/nginx/conf.d/default.conf

# Копіювання нашої конфігурації
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/fastcgi-php.conf /etc/nginx/fastcgi-php.conf

# Створення користувача nginx
RUN addgroup -g 1000 laravel && \
    adduser -G laravel -g laravel -s /bin/sh -D laravel

# Налаштування прав
RUN chown -R laravel:laravel /var/www/html
