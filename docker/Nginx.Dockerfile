FROM "nginx:1.13.6-alpine"

COPY ./docker/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/fastcgi-php.conf /etc/nginx/fastcgi-php.conf