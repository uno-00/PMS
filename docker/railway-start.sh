#!/bin/sh
set -e

PORT="${PORT:-8080}"

echo "[railway] boot — PORT=${PORT} DB_HOST=${DB_HOST:-<unset>} DB_DATABASE=${DB_DATABASE:-<unset>}"

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "[railway] generating APP_KEY..."
    php artisan key:generate --force
fi

echo "[railway] waiting for database..."
tries=0
until php -r "
    \$host = getenv('DB_HOST') ?: '';
    \$port = getenv('DB_PORT') ?: '3306';
    \$db = getenv('DB_DATABASE') ?: '';
    \$user = getenv('DB_USERNAME') ?: '';
    \$pass = getenv('DB_PASSWORD') ?: '';
    if (\$host === '' || \$db === '' || \$user === '') {
        fwrite(STDERR, 'missing DB_HOST, DB_DATABASE, or DB_USERNAME'.PHP_EOL);
        exit(1);
    }
    new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass);
" >/dev/null 2>&1; do
    tries=$((tries + 1))
    if [ "$tries" -ge 60 ]; then
        echo "[railway] ERROR: database not reachable after 120s — check MySQL service variables on Railway"
        exit 1
    fi
    sleep 2
done
echo "[railway] database is reachable."

echo "[railway] running migrations..."
php artisan migrate --force

php artisan storage:link 2>/dev/null || true

post_boot_tasks() {
    if [ "${RUN_SEED:-false}" = "true" ]; then
        echo "[railway] seeding database (RUN_SEED=true)..."
        php artisan db:seed --force
    fi

    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    echo "[railway] post-boot tasks complete."
}

post_boot_tasks &

echo "[railway] starting HTTP server on 0.0.0.0:${PORT}"
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
