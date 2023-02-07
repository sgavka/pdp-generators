FROM php:8.1.4-cli

COPY php.ini /usr/local/etc/php/

ARG PUID=1000
ENV PUID ${PUID}
ARG PGID=1000
ENV PGID ${PGID}

RUN set -ex && \
    apt-get update -yqq && \
    pecl channel-update pecl.php.net && \
    groupadd -g ${PGID} laradock && \
    useradd -l -u ${PUID} -g laradock -m laradock && \
    usermod -p "*" laradock -s /bin/bash && \
    apt-get install -yqq curl && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# xDebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /server/http/

COPY . .

RUN composer install --prefer-dist --no-interaction

ARG TZ=UTC
ENV TZ ${TZ}

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

ENV LANG=en_US.UTF-8 \
    LANGUAGE=en_US.UTF-8

USER root

WORKDIR /project/
