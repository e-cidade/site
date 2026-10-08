<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Governança editorial

GitHub Issue é a interface de autoria. Pull Request é a unidade formal de revisão e autorização. O merge em `main` é o gesto que autoriza publicação.

## Responsabilidade editorial via CODEOWNERS

A fonte canônica de responsabilidade por revisão é `.github/CODEOWNERS`.

Pull Requests editoriais alteram `source/_posts/**` e, quando houver mídia, `source/assets/images/news/**`. Esses caminhos possuem code owner explícito. O GitHub usa o próprio mecanismo nativo para solicitar revisão aos responsáveis.

Não existe login de reviewer hardcoded em workflow, script ou serviço PHP.

A responsabilidade pode ser transferida trocando o owner no CODEOWNERS. O owner pode ser uma pessoa ou, preferencialmente quando existir um grupo editorial estável, um time da organização com acesso de escrita ao repositório.

## Política de `main`

A branch `main` deve exigir Pull Request. A configuração recomendada é:

- Require a pull request before merging;
- Required approvals: **1**;
- **Require review from Code Owners**;
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

`News editorial review state` também não deve ser check obrigatório de branch: ele sincroniza o estado editorial da Issue e não determina quem revisa o PR.

## Alterações depois da aprovação

Quando um novo commit é adicionado depois da aprovação:

1. a aprovação anterior deve ser invalidada pela proteção de branch;
2. o estado editorial volta a refletir a revisão pendente;
3. o code owner deve conferir a versão atual;
4. o novo approval autoriza a versão efetivamente mergeada.

## Validação operacional

Depois de configurar a proteção de branch:

1. use um PR editorial de teste;
2. confirme que o GitHub solicita automaticamente o code owner;
3. confirme que merge fica bloqueado sem aprovação de code owner;
4. aprove o PR e confirme que os checks continuam necessários;
5. acrescente novo commit depois da aprovação;
6. confirme que a aprovação fica stale/inválida;
7. aprove novamente e faça merge.

A configuração do ruleset/branch protection é uma propriedade administrativa do repositório e não é versionada neste código.
