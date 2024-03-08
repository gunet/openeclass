# syntax=docker/dockerfile:1

# Used to copy what we need
FROM alpine as base
RUN mkdir -p /var/www/html
COPY . /var/www/html/
RUN rm -rf /var/www/html/docker

FROM php:8.2-apache

LABEL gr.gunet.e-class-docker.maintainer="eclass@gunet.gr"
LABEL org.opencontainers.image.source="https://github.com/gunet/openeclass"
LABEL org.opencontainers.image.description="Open e-Class Docker image"

RUN apt-get update && apt-get install -yq --no-install-recommends \
    default-libmysqlclient-dev \
    libbz2-dev \
    libmemcached-dev \
    libsasl2-dev \
    curl \
    git \
    libfreetype6-dev \
    libicu-dev \
    libjpeg-dev \
    libldap2-dev \
    libmemcachedutil2 \
    libpng-dev \
    libpq-dev \
    libxml2-dev \
    libzip-dev \
    vim-tiny && \
	rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

RUN docker-php-ext-install iconv intl mysqli opcache pdo_mysql pdo_pgsql zip gd ldap soap

RUN a2disconf charset localized-error-pages serve-cgi-bin && \
    a2dismod -f access_compat autoindex deflate negotiation status

COPY docker/php/ ${PHP_INI_DIR}/

ARG INSTALL=/var/www/html

COPY --from=base --chown=www-data:www-data /var/www/html/ ${INSTALL}/

COPY --from=composer /usr/bin/composer /usr/bin/composer
USER www-data
RUN cd /var/www/html && \
    COMPOSER_CACHE_DIR=/var/www/html/.cache composer update && \
    COMPOSER_CACHE_DIR=/var/www/html/.cache composer install && \
    rm -rf /var/www/html/.cache

RUN mkdir ${INSTALL}/config && \
    mkdir ${INSTALL}/courses && \
    mkdir ${INSTALL}/video
    # chown -R www-data:www-data ${INSTALL}/
USER root

HEALTHCHECK --interval=10s --timeout=3s --start-period=3s --retries=5 \
    CMD pgrep -u www-data -c apache2

WORKDIR ${INSTALL}

EXPOSE 80

ENV TZ=Europe/Athens
ENV MYSQL_LOCATION=db
ENV MYSQL_ROOT_USER=root
ENV MYSQL_ROOT_PASSWORD=secret
ENV MYSQL_DB=eclass
ENV ADMIN_USERNAME=admin
# Admin password is randomly generated. Othewise can be set with env var
# ADMIN_PASSWORD
ENV PHP_MAX_UPLOAD=256M