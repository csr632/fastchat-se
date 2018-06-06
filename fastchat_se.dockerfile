FROM php:fpm
# https://stackoverflow.com/questions/29245216/write-in-shared-volumes-docker
# https://github.com/docker-library/php/issues/391
RUN usermod -u 1000 www-data \
  && docker-php-ext-install mysqli \
  && docker-php-ext-enable mysqli