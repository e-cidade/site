<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Governança editorial

GitHub Issue é a interface de autoria. Pull Request é a unidade formal de revisão e autorização. O merge em `main` é o gesto que autoriza publicação.

## Reviewer editorial

O workflow `News editorial review state` lê a repository variable:

`EDITORIAL_REVIEWER`

O valor deve ser o login GitHub da pessoa responsável por receber a solicitação de review editorial. Enquanto a variável não estiver definida, o fallback atual é `vitormattos`.

A responsabilidade editorial pode ser transferida sem alteração de código: basta alterar a variável do repositório.

## Política de `main`

A branch `main` deve exigir Pull Request. A configuração recomendada é:

- Require a pull request before merging;
- Required approvals: **1**;
- Dismiss stale pull request approvals when new commits are pushed;
- Require conversation resolution before merging;
- Do not allow bypass para colaboradores comuns;
- impedir force push;
- impedir exclusão da branch.

Para o fluxo editorial, aprovação em comentário ou label não substitui Pull Request Review.

## Checks obrigatórios

Depois da implementação da CI seletiva (#122), os workflows mantêm nomes de checks estáveis mesmo quando uma alteração editorial pode pular as etapas pesadas.

A proteção de `main` pode exigir:

- Build;
- Composer;
- JavaScript;
- PHP coding standards;
- PHP lint;
- PHPUnit;
- PR Preview;
- REUSE;
- SCSS;
- ShellCheck;
- Toolchain.

`Docker` não deve ser obrigatório globalmente porque só existe para mudanças em arquivos relacionados ao ambiente Docker.

`News editorial review state` também não deve ser check obrigatório de branch: ele é um mecanismo de sincronização do estado editorial, não uma prova de integridade do código.

## Alterações depois da aprovação

Quando um novo commit é adicionado depois da aprovação:

1. a aprovação anterior deve ser invalidada pela proteção de branch;
2. o estado editorial volta a refletir a revisão pendente;
3. o reviewer deve conferir a versão atual;
4. o novo approval autoriza a versão efetivamente mergeada.

## Validação operacional

Depois de configurar a proteção de branch:

1. use um PR editorial de teste;
2. confirme que merge fica bloqueado sem approval;
3. aprove o PR e confirme que os checks continuam necessários;
4. acrescente novo commit depois da aprovação;
5. confirme que a aprovação fica stale/inválida;
6. aprove novamente e faça merge.

A configuração do ruleset/branch protection é uma propriedade administrativa do repositório e não é versionada neste código.
