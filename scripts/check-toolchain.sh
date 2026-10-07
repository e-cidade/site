#!/usr/bin/env bash
# SPDX-FileCopyrightText: 2026 e-Cidade community
# SPDX-License-Identifier: AGPL-3.0-or-later
set -euo pipefail

# The PHP snippet is intentionally single-quoted so the shell does not expand PHP variables.
# shellcheck disable=SC2016
expected_php="$(php -r '$config = json_decode(file_get_contents("composer.json"), true, 512, JSON_THROW_ON_ERROR); echo $config["config"]["platform"]["php"];')"
actual_php="$(php -r 'echo PHP_VERSION;')"

expected_node="$(node -p "require('./package.json').volta.node")"
actual_node="$(node -p "process.versions.node")"

docker_php="$(sed -nE 's/^FROM php:([0-9]+\.[0-9]+\.[0-9]+)-cli-bookworm@sha256:[0-9a-f]{64}$/\1/p' .docker/php/Dockerfile)"
docker_node="$(sed -nE 's/^FROM node:([0-9]+\.[0-9]+\.[0-9]+)-bookworm-slim@sha256:[0-9a-f]{64} AS node$/\1/p' .docker/php/Dockerfile)"

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

test "$docker_node" = "$expected_node" || {
    echo "Docker Node mismatch: package.json=$expected_node Dockerfile=$docker_node" >&2
    exit 1
}

grep -Eq '^FROM composer:[0-9]+\.[0-9]+\.[0-9]+@sha256:[0-9a-f]{64} AS composer$' .docker/php/Dockerfile || {
    echo "Docker Composer image must use an exact tag pinned by sha256 digest." >&2
    exit 1
}
