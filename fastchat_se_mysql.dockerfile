FROM mysql:8

ENV MYSQL_ROOT_PASSWORD=123456
# https://github.com/docker-library/mysql/issues/241
ENV MYSQL_ROOT_HOST=%
ENV MYSQL_USER=fastchat
ENV MYSQL_PASSWORD=654321
ENV MYSQL_DATABASE=fastchat_db

COPY ./sources.list /etc/apt/sources.list

VOLUME /var/lib/mysql

COPY ./mysql_config /etc/mysql/

COPY ./mysql_init_scripts /docker-entrypoint-initdb.d/