FROM nginx

COPY ./nginx_config /etc/nginx/
COPY ./sources.list /etc/apt/sources.list
