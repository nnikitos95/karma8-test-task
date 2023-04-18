FROM php:8.1-alpine AS base

RUN apk --no-cache update \
    && apk add --no-cache autoconf g++ make \
    postgresql-dev \
    \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    \
    && docker-php-ext-install pdo_pgsql

ENTRYPOINT ["php"]

FROM base AS supervisord

RUN apk add --no-cache --update supervisor

RUN mkdir -p "/var/log/supervisor"

COPY .docker/supervisord.conf /etc/supervisord.conf

ENTRYPOINT ["/usr/bin/supervisord", "-c",  "/etc/supervisord.conf"]