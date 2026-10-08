#!/usr/bin/env bash
# SPDX-FileCopyrightText: 2026 e-Cidade community
# SPDX-License-Identifier: AGPL-3.0-or-later
set -euo pipefail

build_dir="${SITE_BUILD_DIR:-build_production}"

required_pages=(
  "index.html"
  "sobre/index.html"
  "noticias/index.html"
  "codigo-fonte/index.html"
  "manuais-e-documentacoes/index.html"
  "prestadores-de-servico/index.html"
  "aula-inaugural-do-e-cidade-faz-historia/index.html"
)

for page in "${required_pages[@]}"; do
  test -f "${build_dir}/${page}"
done

test -f "${build_dir}/assets/images/migrated/aula-inaugural.png"
test -f "${build_dir}/robots.txt"
if [[ "${build_dir}" == "build_preview" ]]; then
  test ! -f "${build_dir}/sitemap.xml"
else
  test -f "${build_dir}/sitemap.xml"
fi
find "${build_dir}/assets/build" -type f -name '*.css' -print -quit | grep -q .
find "${build_dir}/assets/build" -type f -name '*.js' -print -quit | grep -q .

if grep -R -n "wp-content/uploads" source/_posts; then
  echo "WordPress media reference found in migrated posts." >&2
  exit 1
fi

if [[ -n "${EXPECTED_BASE_URL:-}" ]]; then
  grep -Fq "${EXPECTED_BASE_URL}" "${build_dir}/index.html"
  grep -Eq 'href="[^"]*/pr-preview/pr-[0-9]+/assets/build/[^"]+\.css"' "${build_dir}/index.html"
  grep -Eq 'src="[^"]*/pr-preview/pr-[0-9]+/assets/build/[^"]+\.js"' "${build_dir}/index.html"

  if grep -Eq '(href|src)="/assets/build/' "${build_dir}/index.html"; then
    echo "Preview build contains root-absolute Vite asset URLs." >&2
    exit 1
  fi

  if grep -R -Eq 'src="/assets/images/' "${build_dir}" --include='*.html'; then
    echo "Preview build contains root-absolute content image URLs." >&2
    exit 1
  fi
fi
