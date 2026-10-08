<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Escopo da CI em Pull Requests editoriais

A CI distingue alterações puramente editoriais de mudanças de código ou infraestrutura.

## Editorial-only

Um Pull Request é considerado editorial-only somente quando **todos** os arquivos alterados estão em:

- `source/_posts/**`
- `source/assets/images/news/**`

Nesse caso continuam obrigatórios os checks que provam a publicação:

- REUSE;
- Build de produção;
- integridade de referências locais;
- PR Preview.

Os workflows de Composer, PHPUnit, PHP lint/coding standards, JavaScript, SCSS, ShellCheck e Toolchain continuam sendo criados, mas encerram com sucesso após a classificação, sem instalar toolchains ou dependências desnecessárias.

Isso preserva nomes de checks estáveis para futura branch protection/rulesets.

## Full CI

Qualquer arquivo fora das duas áreas editoriais acima ativa a bateria completa.

Em particular, mudanças em:

- `composer.json` / `composer.lock`;
- `package.json` / `package-lock.json`;
- workflows;
- PHP, JavaScript, SCSS ou scripts;
- `LICENSES/**`;
- configuração do site;

nunca são classificadas como editorial-only.

## Cache

Os caches continuam sendo chaveados pelos manifests/locks existentes. Como mudanças em dependências são full CI, alteração desses arquivos força os workflows relevantes e produz novas chaves de cache.

## Implementação

A regra fica em `PullRequestChangeClassifier`, coberta por PHPUnit. O script `scripts/ci-scope.php` apenas obtém a lista de arquivos por `git diff` e exporta:

- `editorial_only=true|false`
- `changed_count=<n>`

Os workflows usam esse resultado somente para decidir se as etapas pesadas serão executadas.
