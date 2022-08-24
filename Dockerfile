FROM php:8.1-apache

RUN a2enmod rewrite
RUN service apache2 restart

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY . /var/www/htnml

WORKDIR /var/www/htnml