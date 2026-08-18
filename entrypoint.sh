#!/bin/bash
#!/bin/sh
set -e

# 1. Asignar valor por defecto a PORT si no existe
export PORT=${PORT:-80}

# 2. Generar la configuración de Nginx INMEDIATAMENTE
envsubst '${PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

# 3. Comandos de optimización y migración de Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

# 4. Iniciar Supervisor apuntando a tu archivo personalizado
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/argos.conf