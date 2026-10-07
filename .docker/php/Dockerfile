# SPDX-FileCopyrightText: 2026 e-Cidade community
# SPDX-License-Identifier: AGPL-3.0-or-later

FROM php:8.5.11-cli-bookworm@sha256:819080ed413631f6ec564184dd9acd08d9425bd863b9f8b5876b5ea2147d55e7

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        unzip \
        xz-utils \
    && rm -rf /var/lib/apt/lists/*

COPY package.json /tmp/package.json

RUN set -eux; \
    node_version="$(php -r '$package = json_decode(file_get_contents("/tmp/package.json"), true, 512, JSON_THROW_ON_ERROR); echo $package["volta"]["node"];')"; \
    case "$(dpkg --print-architecture)" in \
        amd64) node_arch='x64' ;; \
        arm64) node_arch='arm64' ;; \
        armhf) node_arch='armv7l' ;; \
        *) echo "Unsupported architecture: $(dpkg --print-architecture)" >&2; exit 1 ;; \
    esac; \
    node_archive="node-v${node_version}-linux-${node_arch}.tar.xz"; \
    curl -fsSLO "https://nodejs.org/dist/v${node_version}/${node_archive}"; \
    curl -fsSLO "https://nodejs.org/dist/v${node_version}/SHASUMS256.txt"; \
    grep " ${node_archive}$" SHASUMS256.txt | sha256sum -c -; \
    tar -xJf "${node_archive}" -C /usr/local --strip-components=1; \
    rm -f "${node_archive}" SHASUMS256.txt /tmp/package.json; \
    node --version; \
    npm --version

ADD --checksum=sha256:3f49c71fa66c79b8b2b96bc0ce92885dafd7946f3b5a46f545b390d6f1617e2c \
    https://github.com/mlocati/docker-php-extension-installer/releases/download/2.12.0/install-php-extensions \
    /usr/local/bin/install-php-extensions

RUN chmod uga+x /usr/local/bin/install-php-extensions \
    && install-php-extensions \
        xdebug \
        zip \
        @composer \
    && rm /usr/local/bin/install-php-extensions

WORKDIR /jigsaw

COPY .docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
ENTRYPOINT ["bash", "/usr/local/bin/entrypoint.sh"]
