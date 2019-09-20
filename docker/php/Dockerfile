FROM php:7.3-fpm

#
# Update
#
RUN apt-get update && apt-get upgrade -y && rm -rf m -rf /var/lib/apt/lists/*

#
# Extensions
#
RUN docker-php-ext-install mysqli pdo pdo_mysql && rm -rf /tmp/*

#
# Composer
#
COPY --from composer:latest /usr/bin/composer /usr/bin/composer

#
# Copy config
#
ADD config.ini $PHP_INI_DIR/conf.d/

#
# Fin
#
WORKDIR /var/www/app
EXPOSE 9000

CMD ["php-fpm"]

