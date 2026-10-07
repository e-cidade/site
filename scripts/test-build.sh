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
test -f "${build_dir}/sitemap.xml"
test -f "${build_dir}/robots.txt"
find "${build_dir}/assets/build" -type f -name '*.css' -print -quit | grep -q .
find "${build_dir}/assets/build" -type f -name '*.js' -print -quit | grep -q .

grep -Fq 'property="og:locale" content="pt_BR"' "${build_dir}/index.html"
grep -Fq 'name="twitter:image"' "${build_dir}/index.html"
grep -Fq 'application/ld+json' "${build_dir}/index.html"
grep -Fq 'property="og:type" content="article"' "${build_dir}/aula-inaugural-do-e-cidade-faz-historia/index.html"
grep -Fq 'property="article:published_time"' "${build_dir}/aula-inaugural-do-e-cidade-faz-historia/index.html"
grep -Fq 'summary_large_image' "${build_dir}/aula-inaugural-do-e-cidade-faz-historia/index.html"
grep -Fq 'noindex,nofollow,noarchive' "${build_dir}/404/index.html"

if grep -R -n "wp-content/uploads" source/_posts; then
  echo "WordPress media reference found in migrated posts." >&2
  exit 1
fi

if [[ -n "${EXPECTED_BASE_URL:-}" ]]; then
  grep -Fq "${EXPECTED_BASE_URL}" "${build_dir}/index.html"
fi
