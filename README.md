<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Site da comunidade e-Cidade

Site estático da comunidade e-Cidade, construído com Jigsaw, Vite e SCSS e publicado por GitHub Actions.

O conteúdo institucional, as notícias e o acervo de imagens migrado do antigo WordPress são mantidos no próprio repositório.

## Desenvolvimento local

Requisitos:

- PHP 8.3+
- Composer
- Node.js 24+
- npm

```bash
composer install
npm ci
npm run dev
```

## Qualidade

A integração contínua é segmentada por responsabilidade:

- Composer: validação e auditoria de dependências PHP;
- PHP: lint e PHPUnit;
- JavaScript: Prettier e testes nativos do Node.js;
- SCSS: compilação isolada com Sass;
- ShellCheck: validação dos scripts shell;
- Build: geração do site e smoke tests;
- REUSE: conformidade SPDX/REUSE.

Para executar localmente:

```bash
composer lint
composer test
npm run lint:js
npm run test:js
npm run test:scss
composer build
composer test:build
shellcheck scripts/*.sh
```

## Conteúdo

- páginas institucionais: `source/*.blade.php`;
- notícias: `source/_posts/*.md`;
- SCSS: `source/_assets/scss/`;
- JavaScript: `source/_assets/js/`;
- mídia editorial migrada: `source/assets/images/migrated/`.

## Preview de pull requests

Cada pull request gera um build isolado e, após os testes, publica um preview em:

`https://site-ecidade.librecode.coop/pr-preview/pr-<numero>/`

O build ocorre no contexto não privilegiado do pull request e o deploy é feito por um `workflow_run`, permitindo previews de forks sem expor credenciais ao código contribuído. Ao fechar o PR, o preview é removido.

## Publicação

A branch `main` é publicada no GitHub Pages. O deploy de produção preserva o diretório `pr-preview/`, evitando apagar previews ativos.

## Licença

O projeto segue AGPL-3.0-or-later e usa metadados SPDX/REUSE. Consulte `LICENSE` e `REUSE.toml`.
