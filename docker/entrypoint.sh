#!/bin/sh
set -e

# All PMS containers (app, queue, scheduler) share the same image. The
# CONTAINER_ROLE env var picked in docker-compose.yml decides what runs.
ROLE="${CONTAINER_ROLE:-app}"

wait_for_db() {
    echo "[entrypoint] waiting for database ${DB_HOST:-mysql}:${DB_PORT:-3306}..."
    until php -r "new PDO('mysql:host=${DB_HOST:-mysql};port=${DB_PORT:-3306}', '${DB_USERNAME}', '${DB_PASSWORD}');" >/dev/null 2>&1; do
        sleep 2
    done
    echo "[entrypoint] database is reachable."
}

if [ ! -f /var/www/html/.env ] && [ -f /var/www/html/.env.example ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

case "$ROLE" in
    app)
        wait_for_db
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        php artisan migrate --force
        php artisan storage:link || true
        exec php-fpm
        ;;
    queue)
        wait_for_db
        exec php artisan queue:work --tries=3 --backoff=10 --sleep=3 --max-time=3600
        ;;
    scheduler)
        wait_for_db
        echo "[entrypoint] running Laravel Scheduler loop (every 60s)."
        while true; do
            php artisan schedule:run --verbose --no-interaction
            sleep 60
        done
        ;;
    *)
        echo "[entrypoint] unknown CONTAINER_ROLE '$ROLE'; running default command."
        exec "$@"
        ;;
esac
