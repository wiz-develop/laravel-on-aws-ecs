#!/usr/bin/env bash

set -e

role=${CONTAINER_ROLE:-api}

if [ "$role" = "api" ]; then
    php-fpm

elif [ "$role" = "batch" ]; then
    echo "✅ Running the scheduled batch..."

    # 任意のコマンドを実行
    exec "$@"

elif [ "$role" = "worker" ]; then
    echo "✅ Running the queue worker..."
    php artisan queue:work --verbose --tries=1 --timeout=60

else
    echo "❌ Could not match the container role \"$role\""
    exit 1
fi
