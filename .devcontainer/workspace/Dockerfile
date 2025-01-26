# syntax=docker/dockerfile:1

FROM public.ecr.aws/amazonlinux/amazonlinux:2023

# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
# Install PHP-CLI
# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
ARG PHP_VERSION=8.3
RUN \
    # Outside of the image, we can use the --mount flag to mount a cache directory
    --mount=type=cache,target=/var/lib/dnf,sharing=locked \
    --mount=type=cache,target=/var/cache/dnf,sharing=locked \
    # NOTE: minimal イメージの場合 cache ディレクトリが /var/cache/yum になるため
    --mount=type=cache,target=/var/cache/yum,sharing=locked \
    dnf upgrade -y && dnf install -y \
    # Install PHP-CLI
    php${PHP_VERSION} \
    php${PHP_VERSION}-{cli,fpm,common,mysqlnd,mbstring,bcmath,gd,zip,soap,xml,pdo} \
    # Development tools
    php${PHP_VERSION}-devel \
    php-pear \
    # Install xdebug
    && pecl install xdebug \
    # Clean up to reduce docker file size
    && pecl clear-cache

# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
# Install Composer
# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
# NOTE: https://getcomposer.org/doc/00-intro.md#docker-image
COPY --from=composer/composer:2-bin /composer /usr/bin/composer

# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
# Move setting files
# ――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――――
COPY ./php.ini /etc/php.ini
COPY ./php.d/ /etc/php.d/

ARG USERNAME=vscode
ARG USER_UID=1000
ARG USER_GID=${USER_UID}

# ********************************************************
# * Anything else you want to do like clean up goes here *
# ********************************************************
RUN \
    # Outside of the image, we can use the --mount flag to mount a cache directory
    --mount=type=cache,target=/var/lib/dnf,sharing=locked \
    --mount=type=cache,target=/var/cache/dnf,sharing=locked \
    dnf upgrade -y && dnf install -y \
    # Install tools for development
    sudo \
    git \
    vim \
    wget \
    tar

# Create the user
RUN groupadd --gid ${USER_GID} ${USERNAME} \
    && useradd --uid ${USER_UID} --gid ${USER_GID} -m ${USERNAME} \
    && echo ${USERNAME} ALL=\(root\) NOPASSWD:ALL > /etc/sudoers.d/${USERNAME} \
    && chmod 0440 /etc/sudoers.d/${USERNAME}

# [Optional] Set the default user. Omit if you want to keep the default as root.
USER ${USERNAME}
