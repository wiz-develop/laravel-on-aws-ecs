# syntax=docker/dockerfile:1

# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
# For base
# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
FROM public.ecr.aws/amazonlinux/amazonlinux:2023-minimal AS base

ARG USERNAME=laravel-on-aws-ecs
ARG USER_UID=1000
ARG USER_GID=${USER_UID}

RUN \
    # Outside of the image, we can use the --mount flag to mount a cache directory
    --mount=type=cache,target=/var/lib/dnf,sharing=locked \
    --mount=type=cache,target=/var/cache/dnf,sharing=locked \
    # NOTE: minimal イメージの場合 cache ディレクトリが /var/cache/yum になるため
    --mount=type=cache,target=/var/cache/yum,sharing=locked \
    dnf upgrade -y && dnf install -y \
    # Install Apache
    httpd \
    # For adding user/group commands
    shadow-utils \
    # For setcap command
    libcap \
    # NOTE: 非rootユーザーで特権ポート(1024以下)をバインドするために必要な設定
    && setcap 'cap_net_bind_service=+ep' /usr/sbin/httpd \
    # Create the user
    && groupadd --gid ${USER_GID} ${USERNAME} \
    && useradd --uid ${USER_UID} --gid ${USER_GID} -m ${USERNAME} \
    # Clean up
    && dnf remove -y shadow-utils \
    # マウントするボリュームのディレクトリを作成しておく。マウントしたときに所有者がrootになるのを防ぐため。
    && mkdir -p \
    # Unixソケット通信用のディレクトリ
    /run/php-fpm \
    # 非rootユーザーでApacheを実行するため、所有権を変更
    && chown -R ${USERNAME}:${USERNAME} \
    /run/httpd \
    /run/php-fpm

# Apache の設定ファイルをコピー
COPY --from=apache-ctx ./conf/ /etc/httpd/conf/
COPY --from=apache-ctx ./conf.d/ /etc/httpd/conf.d/

COPY --from=apache-ctx --chmod=0755 ./healthcheck.txt /var/www/healthcheck.txt

# Apache の起動スクリプトをコピー
COPY --from=apache-ctx --chmod=0755 ./httpd-foreground /usr/local/bin/httpd-foreground

WORKDIR /var/www/src

# https://httpd.apache.org/docs/2.4/stopping.html#gracefulstop
STOPSIGNAL SIGWINCH

EXPOSE 80
CMD ["httpd-foreground"]

# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
# For development
# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
FROM base AS development

RUN \
    # Outside of the image, we can use the --mount flag to mount a cache directory
    --mount=type=cache,target=/var/lib/dnf,sharing=locked \
    --mount=type=cache,target=/var/cache/dnf,sharing=locked \
    # NOTE: minimal イメージの場合 cache ディレクトリが /var/cache/yum になるため
    --mount=type=cache,target=/var/cache/yum,sharing=locked \
    dnf upgrade -y && dnf install -y \
    # Install for debugging
    sudo \
    tar \
    procps-ng \
    # Add user to sudoers
    && echo ${USERNAME} ALL=\(root\) NOPASSWD:ALL > /etc/sudoers.d/${USERNAME} \
    && chmod 0440 /etc/sudoers.d/${USERNAME}

# NOTE: httpdプロセスを実行するユーザーを指定
USER ${USERNAME}

# 他コンテナとソケット通信を行うためのボリュームを定義
VOLUME ["/run/php-fpm"]

# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
# For production
# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
FROM base AS production

RUN \
    # マウントするボリュームのディレクトリを作成しておく。マウントしたときに所有者がrootになるのを防ぐため。
    mkdir -p \
    # laravelソースコードのディレクトリ
    /var/www/src \
    # laravelアプリケーションの永続データ保存用のディレクトリ
    /mnt/storage/app/public \
    /mnt/storage/framework/cache/data \
    /mnt/storage/framework/sessions \
    # 作成したディレクトリの所有者を実行ユーザーに変更
    && chown -R ${USERNAME}:${USERNAME} \
    /var/www/src \
    /mnt/storage

# NOTE: httpdプロセスを実行するユーザーを指定
USER ${USERNAME}

# 他コンテナとソケット通信を行うためのボリュームを定義（NOTE: ECS Fargate専用）
# PHP-FPMイメージのデータボリュームをマウントするための記述（NOTE: ECS Fargate専用）
# NOTE: https://docs.aws.amazon.com/AmazonECS/latest/developerguide/bind-mounts.html#bind-mount-examples
VOLUME ["/run/php-fpm", "/var/www/src"]
