# syntax=docker/dockerfile:1

########################
# 1) Copy source (Alpine)
########################
FROM alpine:3.20 AS base
RUN mkdir -p /var/www/html
WORKDIR /var/www/html
COPY . .
RUN rm -rf docker   # don't bake dev helpers into the final image

########################
# 2) Composer binary stage
########################
FROM composer:2 AS composer

########################
# 3) Runtime image
########################
FROM php:8.2-apache

LABEL gr.gunet.e-class-docker.maintainer="eclass@gunet.gr"
LABEL org.opencontainers.image.source="https://github.com/gunet/openeclass"
LABEL org.opencontainers.image.description="Open e-Class Docker image"

# ── OS & PHP extensions ──────────────────────────────────────────────────
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      default-libmysqlclient-dev libbz2-dev libmemcached-dev libsasl2-dev \
      curl git vim-tiny \
      libfreetype6-dev libicu-dev libjpeg-dev libldap2-dev \
      libmemcachedutil2 libpng-dev libpq-dev libxml2-dev libzip-dev \
 && docker-php-ext-install -j"$(nproc)" \
      iconv intl mysqli opcache pdo_mysql pdo_pgsql zip gd ldap soap \
 && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# ── Apache tweaks ────────────────────────────────────────────────────────
RUN a2disconf charset localized-error-pages serve-cgi-bin \
 && a2dismod -f access_compat autoindex deflate negotiation status \
 && echo 'ServerName localhost' > /etc/apache2/conf-available/servername.conf \
 && a2enconf servername

# ── PHP-CLI configuration snippets (optional) ────────────────────────────
COPY docker/php/ ${PHP_INI_DIR}/

# ── Application code from the “base” stage ───────────────────────────────
ARG INSTALL_DIR=/var/www/html
COPY --from=base --chown=www-data:www-data /var/www/html/ ${INSTALL_DIR}/

# ── Composer binary (single file) ────────────────────────────────────────
COPY --from=composer /usr/bin/composer /usr/bin/composer

# ── Create writable directories that will be volume-mounted ──────────────
RUN mkdir -p ${INSTALL_DIR}/config \
             ${INSTALL_DIR}/courses \
             ${INSTALL_DIR}/video \
 && chown -R www-data:www-data ${INSTALL_DIR}

# ── Self-healing entry-point ─────────────────────────────────────────────
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint"]

# ── Health-check & defaults ──────────────────────────────────────────────
HEALTHCHECK --interval=10s --timeout=3s --start-period=3s --retries=5 \
  CMD pgrep -u www-data -c apache2

WORKDIR ${INSTALL_DIR}
EXPOSE 80

ENV TZ=Europe/Athens \
    MYSQL_LOCATION=db \
    MYSQL_ROOT_USER=root \
    MYSQL_ROOT_PASSWORD=secret \
    MYSQL_DB=eclass \
    ADMIN_USERNAME=admin \
    PHP_MAX_UPLOAD=256M