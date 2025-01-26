# syntax=docker/dockerfile:1

# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
# For base
# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
FROM public.ecr.aws/amazonlinux/amazonlinux:2023-minimal AS base

ARG USERNAME=laravel-on-aws-ecs
ARG USER_UID=1000
ARG USER_GID=${USER_UID}

ARG PHP_VERSION=8.3

RUN \
    # Outside of the image, we can use the --mount flag to mount a cache directory
    --mount=type=cache,target=/var/lib/dnf,sharing=locked \
    --mount=type=cache,target=/var/cache/dnf,sharing=locked \
    # NOTE: minimal イメージの場合 cache ディレクトリが /var/cache/yum になるため
    --mount=type=cache,target=/var/cache/yum,sharing=locked \
    dnf upgrade -y && dnf install -y \
    # For self healthchecking (Only for FPM)
    fcgi \
    # For self healthchecking using `pgrep` command (For queue worker process)
    procps-ng  \
    # NOTE: For Snappy PDF (※laravel-snappy を利用しない場合は不要です)
    ipa-gothic-fonts \
    ipa-pgothic-fonts \
    # Install PHP-FPM
    php${PHP_VERSION} \
    php${PHP_VERSION}-{fpm,common,mysqlnd,mbstring,bcmath,gd,zip,soap,xml,pdo} \
    # NOTE: PHP を minimal イメージで実行するために不足しているパッケージを追加している
    # https://github.com/amazonlinux/amazon-linux-2023/issues/481#issuecomment-1743110992
    tzdata \
    # For adding user/group
    shadow-utils \
    # Create the user
    && groupadd --gid ${USER_GID} ${USERNAME} \
    && useradd --uid ${USER_UID} --gid ${USER_GID} -m ${USERNAME} \
    # Clean up
    && dnf remove -y shadow-utils \
    # Unixソケット通信用のディレクトリを作成
    && mkdir -p \
    /run/php-fpm \
    && chown -R ${USERNAME}:${USERNAME} \
    /run/php-fpm

# PHP-FPM の設定ファイルをコピー
COPY --from=php-fpm-ctx ./php-fpm.conf /etc/php-fpm.conf
COPY --from=php-fpm-ctx ./php-fpm.d/www.conf /etc/php-fpm.d/www.conf
COPY --from=php-fpm-ctx ./php.d/10-opcache.ini /etc/php.d/10-opcache.ini

# PHP-FPM の起動スクリプトをコピー
COPY --from=php-fpm-ctx --chmod=0755 ./docker-php-entrypoint /usr/local/bin/docker-php-entrypoint

WORKDIR /var/www/src

# Override stop signal to stop process gracefully
# https://github.com/php/php-src/blob/17baa87faddc2550def3ae7314236826bc1b1398/sapi/fpm/php-fpm.8.in#L163
STOPSIGNAL SIGQUIT

ENTRYPOINT ["docker-php-entrypoint"]

# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
# For builder
# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
FROM base AS builder

# NOTE: https://getcomposer.org/doc/00-intro.md#docker-image
COPY --from=composer/composer:2-bin /composer /usr/bin/composer

# Composer 依存パッケージインストール
# NOTE: composer依存関係の変更はソースの変更よりも頻度が少ないため、ソースをコピーする前に実行している
RUN \
    --mount=type=bind,from=src-ctx,source=./composer.json,target=./composer.json \
    --mount=type=bind,from=src-ctx,source=./composer.lock,target=./composer.lock \
    --mount=type=cache,target=/root/.cache/composer,sharing=locked \
    composer install --no-dev --no-scripts --no-autoloader

# ソースをコピー
COPY --from=src-ctx ./ ./

RUN \
    # NOTE: セッション、画像ファイルの出力先を外部ボリュームへのシンボリックリンクにする
    # コンテナを再起動してもデータが消えないようにするため
    rm -rf \
    ./storage/app/public \
    ./storage/framework/cache/data \
    ./storage/framework/sessions \
    && ln -s /mnt/storage/app/public ./storage/app/public \
    && ln -s /mnt/storage/framework/cache/data ./storage/framework/cache/data \
    && ln -s /mnt/storage/framework/sessions ./storage/framework/sessions \
    # NOTE: vendor/composer 配下に autoload を利用するためのファイルを生成している
    # 通常は composer install 時に生成されるが、上記では　--no-autoloader オプションを付けているため生成されない
    && composer dump-autoload \
    # NOTE: /config/filesystems.php の `link` で定義されているシンボリックリンクをすべて作成
    # 画像などの静的ファイルをクライアントから読み込むために必要
    # WARN: ⚠️ AWS S3などの外部ストレージを利用する場合は不要です
    && php artisan storage:link

# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
# For export
# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
FROM scratch AS export

ARG USER_UID=1000
ARG USER_GID=${USER_UID}
COPY --from=builder --chown=${USER_UID}:${USER_GID} /var/www/src /

# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
# For development
# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
FROM base AS development

RUN \
    # Outside of the image, we can use the --mount flag to mount a cache directory
    --mount=type=cache,target=/var/lib/dnf,sharing=locked \
    # NOTE: minimal イメージの場合 cache ディレクトリが /var/cache/yum になるため
    --mount=type=cache,target=/var/cache/yum,sharing=locked \
    --mount=type=cache,target=/var/cache/dnf,sharing=locked \
    dnf upgrade -y && dnf install -y \
    # Install PHP-FPM for the only development
    php${PHP_VERSION}-devel \
    php-pear \
    # Install for debugging
    sudo \
    tar \
    procps-ng \
    # Install xdebug
    && pecl install xdebug \
    # Clean up to reduce docker file size
    && pecl clear-cache \
    # Add user to sudoers
    && echo ${USERNAME} ALL=\(root\) NOPASSWD:ALL > /etc/sudoers.d/${USERNAME} \
    && chmod 0440 /etc/sudoers.d/${USERNAME}

# NOTE: https://getcomposer.org/doc/00-intro.md#docker-image
COPY --from=composer/composer:2-bin /composer /usr/bin/composer

# 設定ファイルをコピー
COPY --from=php-fpm-ctx ./development.php.ini /etc/php.ini
COPY --from=php-fpm-ctx ./php.d/20-xdebug.ini /etc/php.d/20-xdebug.ini

# コンテナを初期化するスクリプトをコピー
COPY --from=php-fpm-ctx --chmod=0755 ./setup.development.sh ./start.sh /usr/local/bin/

CMD ["bash", "-c", "/usr/local/bin/setup.development.sh && /usr/local/bin/start.sh"]

# NOTE: php-fpmプロセスを実行するユーザーを指定
USER ${USERNAME}

# 他コンテナとソケット通信を行うためのボリュームを定義
VOLUME ["/run/php-fpm"]

# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
# For production
# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
FROM base AS production

RUN \
    # Outside of the image, we can use the --mount flag to mount a cache directory
    --mount=type=cache,target=/var/lib/dnf,sharing=locked \
    --mount=type=cache,target=/var/cache/dnf,sharing=locked \
    # NOTE: minimal イメージの場合 cache ディレクトリが /var/cache/yum になるため
    --mount=type=cache,target=/var/cache/yum,sharing=locked \
    dnf upgrade -y && dnf install -y \
    # https://github.com/laravel-on-aws-ecs/laravel-on-aws-ecs-backend/issues/8
    # WARN: 標準的な文字エンコーディング（iconv）では、変換ができない文字があるため glibc-gconv-extra が必要
    # 例えば、以下の php スクリプトを実行するとエラーが発生する
    # ```php
    # //　文字化け回避
    # stream_filter_prepend($stream,'convert.iconv.utf-8/cp932//TRANSLIT');
    # ```
    # 補足: glibc-gconv-extra は php${PHP_VERSION}-devel パッケージには含まれている
    glibc-gconv-extra \
    # マウントするボリュームのディレクトリを作成しておく。マウントしたときに所有者がrootになるのを防ぐため。
    && mkdir -p \
    # laravelアプリケーションの永続データ保存用のディレクトリ
    /mnt/storage/app/public \
    /mnt/storage/framework/cache/data \
    /mnt/storage/framework/sessions \
    # 作成したディレクトリの所有者を実行ユーザーに変更
    && chown -R ${USERNAME}:${USERNAME} \
    /mnt/storage

# ソースコードをコピー
COPY --from=builder --chown=${USERNAME}:${USERNAME} /var/www/src /var/www/src

# 設定ファイルをコピー
COPY --from=php-fpm-ctx ./production.php.ini /etc/php.ini

# コンテナを初期化するスクリプトをコピー
COPY --from=php-fpm-ctx --chmod=0755 ./setup.production.sh ./start.sh /usr/local/bin/

CMD ["bash", "-c", "/usr/local/bin/setup.production.sh && /usr/local/bin/start.sh"]

# NOTE: php-fpmプロセスを実行するユーザーを指定
USER ${USERNAME}

# 他コンテナとソケット通信を行うためのボリュームを定義（NOTE: ECS Fargate専用）
# PHP-FPMイメージのデータボリュームをマウントするための記述（NOTE: ECS Fargate専用）
# NOTE: https://docs.aws.amazon.com/AmazonECS/latest/developerguide/bind-mounts.html#bind-mount-examples
VOLUME ["/run/php-fpm", "/var/www/src"]
