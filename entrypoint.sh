#!/bin/bash
set -e
php artisan migrate --force
php artisan config:cache
php artisan route:cache
exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf