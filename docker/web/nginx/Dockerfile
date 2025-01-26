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
    # Install Nginx
    nginx \
    # For adding user/group commands
    shadow-utils \
    # For docker-entrypoint script of Nginx
    findutils \
    # For setcap command
    libcap \
    # NOTE: 非rootユーザーで特権ポート(1024以下)をバインドするために必要な設定
    && setcap 'cap_net_bind_service=+ep' /usr/sbin/nginx \
    # Create the user
    && groupadd --gid ${USER_GID} ${USERNAME} \
    && useradd --uid ${USER_UID} --gid ${USER_GID} -m ${USERNAME} \
    # Clean up
    && dnf remove -y shadow-utils \
    # マウントするボリュームのディレクトリを作成しておく。マウントしたときに所有者がrootになるのを防ぐため。
    && mkdir -p \
    # Unixソケット通信用のディレクトリ
    /run/php-fpm \
    # Nginx用のディレクトリ
    /docker-entrypoint.d \
    /var/cache/nginx \
    # 非rootユーザーでNginxを実行するため、所有権を変更
    && chown -R ${USERNAME}:${USERNAME} \
    # /run/nginx \
    /run/php-fpm \
    /docker-entrypoint.d \
    /var/cache/nginx \
    /etc/nginx \
    # 非rootユーザーでNginxを実行するため、アクセス権を変更
    && chmod -R g+w \
    /var/cache/nginx \
    /etc/nginx \
    # NOTE: リクエストログとエラーログをdockerログに転送するためシンボリックリンクを作成
    && ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

# Nginx の設定ファイルをコピー
COPY --from=nginx-ctx ./nginx.conf /etc/nginx/nginx.conf
COPY --from=nginx-ctx ./conf.d/ /etc/nginx/conf.d/
COPY --from=nginx-ctx ./default.d/ /etc/nginx/default.d/

# Nginx の起動スクリプトをコピー
COPY --from=nginx-ctx --chmod=0755 ./docker-entrypoint.d/ /docker-entrypoint.d/
COPY --from=nginx-ctx --chmod=0755 ./docker-entrypoint.sh /
ENTRYPOINT ["/docker-entrypoint.sh"]

WORKDIR /var/www/src

EXPOSE 80

STOPSIGNAL SIGQUIT

CMD ["nginx", "-g", "daemon off;"]

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
