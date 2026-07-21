#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../backend"
exec php -d upload_max_filesize=25M -d post_max_size=30M -d memory_limit=512M \
    artisan serve --host=127.0.0.1 --port="${APP_PORT:-8000}" "$@"
