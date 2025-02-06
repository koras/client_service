FROM alpine:latest

WORKDIR /var/www/html/

# Essentials
RUN apk add --no-cache zip unzip curl nginx supervisor tzdata php-bcmath

# Set timezone to Moscow (+3)
RUN cp /usr/share/zoneinfo/Europe/Moscow /etc/localtime && echo "Europe/Moscow" > /etc/timezone

# Installing bash
RUN apk add bash
RUN sed -i 's/bin\/ash/bin\/bash/g' /etc/passwd

# Установка Memcached
#RUN apk add --no-cache memcached

# Установите пакет crond
#RUN apk add --no-cache dcron


# Installing PHP
RUN apk add --no-cache php83 \
    php83-common \
    php83-fpm \
    php83-pdo \
    php83-opcache \
    php83-zip \
    php83-phar \
    php83-iconv \
    php83-cli \
    php83-curl \
    php83-openssl \
    php83-mbstring \
    php83-tokenizer \
    php83-fileinfo \
    php83-json \
    php83-xml \
    php83-xmlwriter \
    php83-simplexml \
    php83-dom \
    php83-pdo_mysql \
    php83-pdo_pgsql \
    php83-tokenizer \
    php83-pecl-redis \
    php83-gd \
    php83-pecl-imagick \
    php83-xmlreader \
#    php83-pecl-memcached \
    ;

#RUN ln -s /usr/bin/php83 /usr/bin/php

RUN wget "https://storage.yandexcloud.net/cloud-certs/CA.pem" \
     --output-document /var/www/html/root.crt && \
    chmod 0600 /var/www/html/root.crt

# Installing composer
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN rm -rf composer-setup.php

# Configure supervisor
RUN mkdir -p /etc/supervisor.d/
COPY .docker/supervisord.ini /etc/supervisor.d/supervisord.ini

# Configure PHP
RUN mkdir -p /run/php/
RUN touch /run/php/php8.2-fpm.pid

# Configure Memcached
#COPY .docker/memcached.conf /etc/memcached.conf

COPY .docker/php-fpm.conf /etc/php83/php-fpm.conf
COPY .docker/php.ini-production /etc/php83/php.ini

# Configure nginx
COPY .docker/nginx.conf /etc/nginx/
COPY .docker/nginx-laravel.conf /etc/nginx/modules/

RUN mkdir -p /run/nginx/
RUN touch /run/nginx/nginx.pid

RUN ln -sf /dev/stdout /var/log/nginx/access.log
RUN ln -sf /dev/stderr /var/log/nginx/error.log

# Building process
COPY . .
RUN composer install --no-dev

RUN chown -R nobody:nobody /var/www/html/storage
RUN chmod -R 777 /var/lib/nginx/tmp

RUN mkdir -p /var/www/tmp
RUN chown -R nobody:nobody /var/www/tmp
RUN chmod -R 777 /var/www/tmp

# Права к папке с временными файлами nginx
RUN mkdir -p /var/lib/nginx/tmp
RUN chown -R nobody:nobody /var/lib/nginx/tmp
RUN chmod -R 777 /var/lib/nginx/tmp

# Создайте файл cron
#COPY .docker/cron /etc/crontabs/root

# Дайте правильные разрешения для файла cron
#RUN chmod 0600 /etc/crontabs/root

EXPOSE 80
#CMD ["sh", "-c", "crond && supervisord -c /etc/supervisor.d/supervisord.ini"]
CMD ["sh", "-c", "supervisord -c /etc/supervisor.d/supervisord.ini"]

