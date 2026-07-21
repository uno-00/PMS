#!/bin/sh
set -e

PORT="${PORT:-8080}"

echo "[railway] waiting for database..."
until php -r "
    \$host = getenv('DB_HOST') ?: '127.0.0.1';
    \$port = getenv('DB_PORT') ?: '3306';
    \$db = getenv('DB_DATABASE') ?: '';
    \$user = getenv('DB_USERNAME') ?: '';
    \$pass = getenv('DB_PASSWORD') ?: '';
    new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass);
" >/dev/null 2>&1; do
    sleep 2
done
echo "[railway] database is reachable."

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    php artisan key:generate --force
fi

php artisan migrate --force

if [ "${RUN_SEED:-true}" = "true" ]; then
    php artisan db:seed --force
fi

php artisan storage:link 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[railway] starting HTTP server on 0.0.0.0:${PORT}"
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
