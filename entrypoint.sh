#!/bin/bash

set -e


envsubst '$PORT' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf


php artisan migrate --force


php artisan config:cache
php artisan route:cache


exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
