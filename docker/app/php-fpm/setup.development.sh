#!/usr/bin/env bash

set -e

composer install

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# NOTE: laravel 10.x～
php artisan storage:unlink

# NOTE: /config/filesystems.php の `link` で定義されているシンボリックリンクをすべて作成する
# 画像などの静的ファイルをクライアントから読み込むために必要
# WARN: ⚠️ AWS S3などの外部ストレージを利用する場合は不要です
php artisan storage:link
