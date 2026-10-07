#!/usr/bin/env bash
# SPDX-FileCopyrightText: 2026 e-Cidade community
# SPDX-License-Identifier: AGPL-3.0-or-later
set -euo pipefail

# shellcheck disable=SC2016 -- the PHP snippet is intentionally single-quoted.
expected_php="$(php -r '$config = json_decode(file_get_contents("composer.json"), true, 512, JSON_THROW_ON_ERROR); echo $config["config"]["platform"]["php"];')"
actual_php="$(php -r 'echo PHP_VERSION;')"

expected_node="$(node -p "require('./package.json').volta.node")"
actual_node="$(node -p "process.versions.node")"

docker_php="$(sed -nE 's/^FROM php:([0-9]+\.[0-9]+\.[0-9]+)-cli-bookworm@sha256:[0-9a-f]{64}$/\1/p' .docker/php/Dockerfile)"

test "$actual_php" = "$expected_php" || {
    echo "PHP runtime mismatch: expected $expected_php, got $actual_php" >&2
    exit 1
}

test "$actual_node" = "$expected_node" || {
    echo "Node runtime mismatch: expected $expected_node, got $actual_node" >&2
    exit 1
}

test "$docker_php" = "$expected_php" || {
    echo "Docker PHP mismatch: composer.json=$expected_php Dockerfile=$docker_php" >&2
    exit 1
}

grep -Eq '^FROM php:[0-9]+\.[0-9]+\.[0-9]+-cli-bookworm@sha256:[0-9a-f]{64}$' .docker/php/Dockerfile || {
    echo "Docker base image must use an exact PHP tag pinned by sha256 digest." >&2
    exit 1
}

grep -Fq 'releases/download/2.12.0/install-php-extensions' .docker/php/Dockerfile || {
    echo "docker-php-extension-installer must use an explicit release." >&2
    exit 1
}

grep -Fq 'ADD --checksum=sha256:3f49c71fa66c79b8b2b96bc0ce92885dafd7946f3b5a46f545b390d6f1617e2c' .docker/php/Dockerfile || {
    echo "docker-php-extension-installer must be checksum-pinned." >&2
    exit 1
}
