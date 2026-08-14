#!/bin/bash
set -e
php artisan migrate --force
php artisan config:cache
php artisan route:cache

envsubst '${PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf