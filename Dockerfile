# syntax=docker/dockerfile:1

# Build stage for PHP dependencies
FROM docker.io/composer:latest AS composer_stage
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs
RUN composer dump-autoload --optimize

# Build stage for JS dependencies using Bun
FROM docker.io/oven/bun:latest AS bun_stage
WORKDIR /app
# Install php-cli for postinstall script
RUN apt-get update && apt-get install -y php-cli
# Copy composer vendor
COPY --from=composer_stage /app/vendor ./vendor
# Copy needed app files
COPY js js
COPY include include
COPY package.json .
# Install JS dependencies and run postinstall script (php ./js/build/build.php)
RUN bun install --frozen-lockfile

# Final stage
FROM alpine:3.23

LABEL gr.gunet.e-class-docker.maintainer="eclass@gunet.gr"
LABEL org.opencontainers.image.source="https://github.com/gunet/openeclass"
LABEL org.opencontainers.image.description="Open e-Class Docker image"

# Install Nginx, PHP8.4 and extensions from packages
RUN apk add --no-cache \
    nginx \
    php84 \
    php84-fpm \
    php84-mysqli \
    php84-pdo_mysql \
    php84-intl \
    php84-zip \
    php84-gd \
    php84-ldap \
    php84-soap \
    php84-opcache \
    php84-iconv \
    php84-ctype \
    php84-dom \
    php84-session \
    php84-xml \
    php84-simplexml \
    php84-mbstring \
    php84-curl \
    php84-fileinfo \
    php84-json \
    php84-xmlreader \
    php84-xmlwriter \
    php84-tokenizer \
    php84-openssl \
    php84-bz2 \
    php84-pdo \
    supervisor \
    curl \
    git \
    tzdata \
    sed

# Symlink php84 to php
RUN ln -sf /usr/bin/php84 /usr/bin/php

WORKDIR /var/www/html

# Copy Open eClass files
COPY --exclude=.hg --exclude=config --exclude=courses --exclude=video --exclude=storage . .

# Copy application files from bun_stage (which includes vendor and built assets)
COPY --from=bun_stage --chown=nginx:nginx /app /var/www/html

# Configuration
COPY docker/php/php.ini /etc/php84/conf.d/custom.ini
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh && \
    # Configure PHP-FPM to listen on 9000 and run as nginx user
    sed -i 's/listen = .*/listen = 127.0.0.1:9000/' /etc/php84/php-fpm.d/www.conf && \
    sed -i 's/user = .*/user = nginx/' /etc/php84/php-fpm.d/www.conf && \
    sed -i 's/group = .*/group = nginx/' /etc/php84/php-fpm.d/www.conf && \
    # Set permissions and create needed directories
    mkdir -p /var/lib/nginx/tmp && \
    chown -R nginx:nginx /var/lib/nginx && \
    mkdir -p config courses video storage && \
    chown -R nginx:nginx config courses video storage

EXPOSE 80

ENV TZ=Europe/Athens
ENV MYSQL_LOCATION=db
ENV MYSQL_ROOT_USER=root
ENV MYSQL_ROOT_PASSWORD=secret
ENV MYSQL_DB=eclass
ENV ADMIN_USERNAME=admin
ENV PHP_MAX_UPLOAD=256M

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
