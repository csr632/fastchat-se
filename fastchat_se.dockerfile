FROM php:fpm
# https://stackoverflow.com/questions/29245216/write-in-shared-volumes-docker
RUN usermod -u 1000 www-data