#!/bin/sh

# Set PHP_MAX_UPLOAD in php.ini
if [ ! -z "$PHP_MAX_UPLOAD" ]; then
    sed -i "s/\${PHP_MAX_UPLOAD}/$PHP_MAX_UPLOAD/g" /etc/php84/conf.d/custom.ini
    sed -i "s/client_max_body_size .*/client_max_body_size $PHP_MAX_UPLOAD;/" /etc/nginx/http.d/default.conf
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf
