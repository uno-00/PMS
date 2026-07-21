#!/bin/sh
set -e

PORT="${PORT:-8080}"

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "[railway] generating APP_KEY..."
    php artisan key:generate --force
fi

setup_database() {
    echo "[railway] waiting for database..."
    tries=0
    until php -r "
        \$host = getenv('DB_HOST') ?: '127.0.0.1';
        \$port = getenv('DB_PORT') ?: '3306';
        \$db = getenv('DB_DATABASE') ?: '';
        \$user = getenv('DB_USERNAME') ?: '';
        \$pass = getenv('DB_PASSWORD') ?: '';
        new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass);
    " >/dev/null 2>&1; do
        tries=$((tries + 1))
        if [ "$tries" -ge 90 ]; then
            echo "[railway] ERROR: database not reachable after 180s"
            return 1
        fi
        sleep 2
    done

    echo "[railway] running migrations..."
    php artisan migrate --force

    if [ "${RUN_SEED:-false}" = "true" ]; then
        echo "[railway] seeding database (RUN_SEED=true)..."
        php artisan db:seed --force
    fi

    php artisan storage:link 2>/dev/null || true
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    echo "[railway] database setup complete."
}

setup_database &
SETUP_PID=$!

echo "[railway] starting HTTP server on 0.0.0.0:${PORT} (migrations continue in background)"
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
