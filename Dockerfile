FROM php:8.1-fpm-alpine

RUN apk update && apk add --no-cache --update bash nodejs npm wget g++ git openssh icu-dev libpq-dev libzip-dev libpng-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install intl opcache pdo pdo_mysql pdo_pgsql gd zip acl exif pcntl bcmath

WORKDIR /var/www/app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash
RUN apk add symfony-cli && npm install -g @vue/cli
RUN git config --global user.email "contact@gmail.com" && git config --global user.name "Hippo A."
