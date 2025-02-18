#!/usr/bin/env bash

# プロジェクトのソースコードディレクトリに移動
cd ${CONTAINER_SRC_PATH}

# Automatic PHPDoc generation for Laravel Facades
# https://github.com/barryvdh/laravel-ide-helper?tab=readme-ov-file#automatic-phpdoc-generation-for-laravel-facades
php artisan ide-helper:generate
