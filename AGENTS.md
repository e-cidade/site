<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# AGENTS.md

Este arquivo concentra as orientações técnicas para agentes e mantenedores que alteram o site do e-Cidade. O README deve permanecer institucional e orientado à proposta de valor do projeto.

## Escopo do repositório

Este repositório contém o site comunitário do e-Cidade, construído com Jigsaw, Vite e SCSS.

- páginas institucionais: `source/*.blade.php`;
- notícias: `source/_posts/*.md`;
- SCSS: `source/_assets/scss/`;
- JavaScript: `source/_assets/js/`;
- mídia editorial: `source/assets/images/migrated/`.

## URLs

- URL institucional: `https://ecidade.softwarepublico.org/`;
- publicação atual do novo site: `https://site-ecidade.librecode.coop/`;
- previews de PR: `https://site-ecidade.librecode.coop/pr-preview/pr-<numero>/`.

Não altere o domínio de produção/CNAME sem validar previamente o DNS e o cutover do site oficial.

## Fontes de verdade do toolchain

- compatibilidade PHP: `composer.json > require.php`;
- runtime PHP: `composer.json > config.platform.php`;
- compatibilidade Node.js: `package.json > engines.node`;
- runtime Node.js: `package.json > volta.node`.

Os workflows devem extrair essas versões das fontes acima em vez de repetir números.

## Dependências PHP isoladas

Ferramentas de qualidade PHP devem ficar em `vendor-bin/<ferramenta>/`, cada uma com `composer.json` e `composer.lock` próprios.

O Dependabot cobre `/vendor-bin/*`; novas ferramentas nesse padrão não precisam de uma entrada adicional.

## Testes

Todos os testes ficam sob `tests/` e devem refletir o caminho do código testado.

Exemplos:

- `listeners/GenerateSitemap.php` → `tests/php/Unit/Listeners/GenerateSitemapTest.php`;
- `source/_assets/js/main.js` → `tests/js/Unit/source/_assets/js/main.test.js`.

Evite criar árvores paralelas como `tests-js/` ou arquivos de teste soltos na raiz.

## Qualidade

Antes de concluir uma alteração, execute ou garanta no CI:

```bash
composer validate --strict
composer audit
composer lint
composer cs:check
composer test

npm ci --no-audit --no-fund
npm audit --audit-level=high
npm run lint:js
npm run test:js
npm run test:scss

composer build
composer test:build
shellcheck scripts/*.sh
```

Os workflows são segmentados por responsabilidade. Não concentre ferramentas independentes em um único workflow se isso dificultar diagnóstico ou manutenção.

## Docker

A imagem de desenvolvimento usa multi-stage com imagens oficiais de Composer, Node e PHP, todas com tag explícita e digest.

O Dockerfile deve permanecer alinhado às versões declaradas em `composer.json` e `package.json`. O workflow `Toolchain` valida esse alinhamento e o workflow `Docker` deve conseguir construir a imagem.

## DCO e assinatura

Todo commit criado por mantenedores ou agentes deve:

- conter `Signed-off-by` compatível com o autor;
- passar no DCO;
- ser criptograficamente assinado quando o fluxo de automação permitir;
- quando disponível, ser criado pelo Git Signing MCP em vez da API comum de commits.

Dependabot e outros bots seguem as verificações próprias configuradas no repositório.

## SPDX / REUSE

Arquivos textuais devem carregar metadados SPDX no próprio arquivo, o mais próximo possível do topo.

- Markdown sem front matter: bloco SPDX no início;
- Markdown/Jigsaw com YAML front matter: front matter primeiro e SPDX imediatamente depois;
- PHP/JS: comentários compatíveis com a linguagem;
- SCSS: comentários CSS;
- YAML, shell, Dockerfile e arquivos equivalentes: `# SPDX-...`;
- XML/SVG: comentário XML.

Use `REUSE.toml` apenas quando o formato não comportar cabeçalho adequadamente, como lockfiles, JSON ou binários. Não use uma anotação global `**` para mascarar arquivos sem cabeçalho.

## Pull requests

PRs devem ser pequenos o suficiente para revisão, descrever impacto funcional e manter todos os checks verdes. O preview deve funcionar antes do merge quando a alteração afetar o site renderizado.
