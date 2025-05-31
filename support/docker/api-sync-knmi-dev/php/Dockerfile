ARG PHP_VERSION=latest
ARG DEBIAN_FRONTEND=noninteractive
#FROM mcr.microsoft.com/mssql-tools as mssql
FROM composer AS composer
FROM mlocati/php-extension-installer AS php-extension-installer
FROM php:8.3-cli
ARG XDEBUG_VERSION

#COPY --from=mssql /opt/microsoft/ /opt/microsoft/
#COPY --from=mssql /opt/mssql-tools/ /opt/mssql-tools/
#COPY --from=mssql /usr/lib/libmsodbcsql-13.so /usr/lib/libmsodbcsql-13.so
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

COPY scripts/install-base.sh /tmp
COPY scripts/install-base-packages-debian.sh /tmp
COPY scripts/install-php-extensions.sh /tmp

RUN set -xe && \
    apt-get update && \
    apt-get -y upgrade && \
    /tmp/install-php-extensions.sh && \
    /tmp/install-base-packages-debian.sh && \
    /tmp/install-base.sh && \
    apt-get clean && \
    rm -rf /tmp/* /var/tmp/* /usr/local/lib/php/doc/* /var/cache/apt/* /var/lib/apt/lists/*

ENV LANG="en_US.utf8"
ENV TZ="Europe/Amsterdam"
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" && \
    ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && \
    echo $TZ > /etc/timezone

RUN localedef -i nl_NL -f UTF-8 nl_NL.UTF-8 && \
    localedef -i en_US -f UTF-8 en_US.UTF-8


RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash && \
    apt install symfony-cli

WORKDIR /application

ENV UID=1000
ENV GID=1000

RUN usermod -u "$UID" www-data \
    && groupmod -g "$GID" www-data \
    && mkdir /home/www-data \
    && chown www-data:www-data /home/www-data \
    && usermod -d /home/www-data www-data

RUN chown -R www-data:www-data /application
