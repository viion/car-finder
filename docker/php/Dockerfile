FROM php:7.3-fpm

#
# Update
#
RUN apt-get update && apt-get upgrade -y

#
# Extensions
#
RUN docker-php-ext-install mysqli pdo pdo_mysql

#
# Cache + Composer
#
RUN rm -rf /var/cache/apk/* && rm -rf /tmp/* && \
    curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

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
