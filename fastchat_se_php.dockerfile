FROM php:fpm

COPY --chown=www-data:www-data ./codeigniter /codeigniter/
COPY --chown=root:staff ./php_config/php-dev.ini /usr/local/etc/php/php.ini
COPY ./sources.list /etc/apt/sources.list

# https://github.com/docker-library/php/issues/391
RUN docker-php-ext-install mysqli \
  && docker-php-ext-enable mysqli 