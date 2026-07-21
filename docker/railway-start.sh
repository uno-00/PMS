#!/bin/sh
set -e

PORT="${PORT:-8080}"

log() {
    echo "[railway] $*"
}

log "boot — PORT=${PORT} APP_ENV=${APP_ENV:-unset} DB_HOST=${DB_HOST:-unset} DB_DATABASE=${DB_DATABASE:-unset}"

case "${DB_HOST:-}" in
    ""|127.0.0.1|localhost)
        log "ERROR: DB_HOST is not configured for Railway."
        log "       Web service → Variables → Add Reference from your MySQL service:"
        log "       DB_HOST=\${{MySQL.MYSQLHOST}} (replace MySQL with your service name)"
        ;;
esac

if [ "${APP_ENV:-production}" = "local" ]; then
    log "WARNING: APP_ENV=local — set APP_ENV=production on Railway"
fi

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    log "generating APP_KEY..."
    php artisan key:generate --force
fi

php artisan storage:link 2>/dev/null || true

setup_database() {
    log "waiting for database..."
    tries=0
    until php -r "
        \$host = getenv('DB_HOST') ?: '';
        \$port = getenv('DB_PORT') ?: '3306';
        \$db = getenv('DB_DATABASE') ?: '';
        \$user = getenv('DB_USERNAME') ?: '';
        \$pass = getenv('DB_PASSWORD') ?: '';
        if (\$host === '' || \$db === '' || \$user === '') {
            exit(1);
        }
        new PDO(
            \"mysql:host=\$host;port=\$port;dbname=\$db;charset=utf8mb4\",
            \$user,
            \$pass,
            [PDO::ATTR_TIMEOUT => 5]
        );
    " >/dev/null 2>&1; do
        tries=$((tries + 1))
        if [ "$tries" -ge 60 ]; then
            log "ERROR: database not reachable after 120s"
            return 1
        fi
        sleep 2
    done

    log "database reachable — running migrations..."
    php artisan migrate --force

    if [ "${RUN_SEED:-false}" = "true" ]; then
        log "seeding database (RUN_SEED=true)..."
        php artisan db:seed --force
    fi

    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    log "database setup complete"
}

setup_database >>/proc/1/fd/1 2>&1 &

log "starting HTTP server on 0.0.0.0:${PORT}"
exec php -S "0.0.0.0:${PORT}" -t public public/railway-router.php
