FROM php:8.2-apache-trixie AS base

ARG INSTALL_DIR="/var/www/html"
ARG COMPOSER_CACHE_DIR="/tmp/composer"

RUN mkdir -p ${INSTALL_DIR}
COPY . ${INSTALL_DIR}
COPY docker/php/php.ini ${PHP_INI_DIR}/php.ini

RUN DEBIAN_FRONTEND=noninteractive apt update && apt install -y --no-install-recommends \
    default-libmysqlclient-dev \
    libbz2-dev \
    libmemcached-dev \
    libsasl2-dev \
    libfreetype6-dev \
    libicu-dev \
    libjpeg-dev \
    libldap2-dev \
    libmemcachedutil2 \
    libpng-dev \
    libpq-dev \
    libxml2-dev \
    libzip-dev \
    git && \
    apt clean

RUN docker-php-ext-install iconv intl mysqli opcache pdo_mysql pdo_pgsql zip gd ldap soap

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN a2disconf charset localized-error-pages serve-cgi-bin && \
    a2dismod -f access_compat autoindex deflate negotiation status

RUN mkdir -p ${COMPOSER_CACHE_DIR}

RUN cd ${INSTALL_DIR} && \
    COMPOSER_CACHE_DIR=${COMPOSER_CACHE_DIR} composer update && \
    COMPOSER_CACHE_DIR=${COMPOSER_CACHE_DIR} composer install

RUN mkdir -p ${INSTALL_DIR}/config ${INSTALL_DIR}/courses ${INSTALL_DIR}/video

RUN chown -R www-data:www-data ${INSTALL_DIR}

LABEL gr.gunet.e-class-docker.maintainer="eclass@gunet.gr"
LABEL org.opencontainers.image.source="https://github.com/gunet/openeclass"
LABEL org.opencontainers.image.description="Open e-Class Docker image"

# Admin password is randomly generated. Othewise can be set with env var
# ADMIN_PASSWORD
ENV TZ=Europe/Athens
ENV MYSQL_LOCATION=db
ENV MYSQL_ROOT_USER=root
ENV MYSQL_ROOT_PASSWORD=secret
ENV MYSQL_DB=eclass
ENV ADMIN_USERNAME=admin
ENV PHP_MAX_UPLOAD=256M

HEALTHCHECK --interval=10s --timeout=3s --start-period=3s --retries=5 \
    CMD pgrep -u www-data -c apache2

EXPOSE 80

VOLUME ["${INSTALL_DIR}/config", "${INSTALL_DIR}/courses", "${INSTALL_DIR}/video"]