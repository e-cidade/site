<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Preparação operacional e critérios de aceite da publicação editorial

Este guia distingue **implementação versionada** de **configuração administrativa** e da **validação real** do fluxo. Os PRs #149 e #151 foram incorporados ao `main`. Ter testes verdes não prova que aprovação de code owner, GitHub App e publicação fim a fim estejam operacionais.

## O que já está implementado

- Issue Form, validação, criação/atualização do mesmo PR editorial, mídias versionadas e licença REUSE;
- snapshot editorial e revisão de notícias já publicadas, preservando slug e data editorial original;
- avisos de preview somente depois de resultado bem-sucedido e teste contra assets inválidos;
- isolamento em worktree, adapter GitHub testável, CI seletiva e testes PHPUnit;
- associação entre publicação e Issue, com atualização de status e fechamento somente após deploy;
- CODEOWNERS declarativo, sem responsável individual hardcoded na automação;
- inventário das 16 imagens migradas com fontes de proveniência e estado de direitos **pendente de comprovação**, sem licença presumida.

## Etapa administrativa A — identidade GitHub App

**Ainda não confirmada no repositório.** Sem esta configuração, atualizações feitas por `GITHUB_TOKEN` podem continuar deixando a CI e o preview do PR editorial em `action_required`.

1. Um administrador cria uma GitHub App para uso editorial e a instala **somente** em `e-cidade/site`.
2. Permissões de repositório: **Contents: Read and write**, **Issues: Read and write**, **Pull requests: Read and write**. `Metadata: Read` é implícita. Não conceder `Administration`, `Actions` ou permissão organizacional além da necessidade.
3. Em **Settings → Secrets and variables → Actions**, configura a **variable** `EDITORIAL_APP_ID` e o **secret** `EDITORIAL_APP_PRIVATE_KEY` com a chave privada PEM correspondente. Não adicionar o PEM, o App ID como segredo de código, nem tokens ao Git.
4. Verifica no workflow `Sync news issue` que **Create editorial GitHub App token** deixou de ser `skipped` e que a autenticação `GH_TOKEN` é efêmera.
5. Com uma edição supervisionada da Issue #102, verifica que a identidade de App atualiza a branch e que a CI/preview do PR #104 inicia sem `Approve workflows to run`. O experimento **não deve ser mergeado** nesta etapa.

Se a App não está instalada ou a configuração não foi comprovada, **não marcar o requisito de identidade como validado**. Não usar `pull_request_target`, bypass ou workflows privilegiados para executar conteúdo de PR como substituto.

## Etapa administrativa B — revisão humana e proteção da main

**Ainda não confirmada no repositório.** Em 08/10/2026 a API retornou zero rulesets e a leitura de branch protection foi negada para a integração (HTTP 403): isso não prova que não exista uma proteção clássica.

1. Definir no CODEOWNERS um **time editorial real** com acesso de escrita e capacidade de revisões independentes; se mantiver pessoas individuais, garantir que haja pelo menos um code owner elegível **diferente do autor**. O autor não pode aprovar o próprio PR. Não criar referência a time inexistente.
2. Administrador abre **Settings → Rules → Rulesets** (ou Branch protection para `main`) e exige:
   - Pull Request antes do merge;
   - ao menos uma aprovação independente e **Require review from Code Owners**;
   - descartar aprovações antigas quando novos commits são enviados;
   - conversas resolvidas e checks pertinentes concluídos;
   - impedir force push e exclusão; sem bypass para colaboradores comuns.
3. Usar os nomes reais dos checks executados nos PRs: `Build`, `Composer`, `JavaScript`, `PHP coding standards`, `PHP lint`, `PHPUnit`, `PR Preview`, `REUSE`, `SCSS`, `ShellCheck` e `Toolchain`. **Não exigir** `Docker` como check global nem `News editorial review state` como aprovação.
4. Validar com PR controlado que a ausência de aprovação **bloqueia** o merge, um review de code owner independente desbloqueia após CI e novo commit torna a aprovação antiga inválida.

Não declarar a proteção implementada com base apenas no arquivo CODEOWNERS: a imposição é uma configuração do GitHub.

## Etapa C — aceite ponta a ponta, reservado à Issue #102

Esta validação será feita **junto com a manutenção humana** do experimento #102/#104, quando o fluxo for retomado. Não editar o corpo da #102, não forçar workflow e não fazer merge do PR #104 automaticamente.

Conferir no roteiro [Smoke test do fluxo editorial](editorial-smoke-test.md), em ordem:

1. identidade da App e CI/preview automático, sem `action_required`;
2. build e assets corretos no preview em subdiretório, status único na Issue;
3. edição inválida bloqueada, correção reusando PR, aprovação e invalidação de aprovação;
4. publicação controlada com merge aprovado de **uma notícia apropriada para publicar** (não presumir que o PR #104 possa ser mergeado);
5. confirmação do deploy, URL, fechamento da Issue de publicação, reabertura/revisão sem mudar slug nem data original.

Se o experimento #104 não deve ser publicado, concluir as etapas de publicação com uma notícia de teste **expressamente aprovada** em outro PR. Não confundir verificação local em PHPUnit com evidência de integração real.

## Acervo histórico e direitos

O PR #149 classificou 16 mídias migradas como `source-located-license-pending`: as fontes editoriais estão documentadas, **mas isso não confirma licença ou permissão**. O relatório `php scripts/audit-news-media.php --markdown` reproduz o inventário. Não inventar detentor, copyright, licença SPDX ou presumir direito de republicação com base na URL de um artigo.

Quando surgir documentação verificável de autorização/licença, fazer PR com metadados REUSE específicos. Quando não houver fundamento suficiente para exposição pública, revisar o caso com os responsáveis e considerar substituição/retirada, preservando SEO e histórico. A conclusão do trabalho de inventário não significa regularização jurídica de todas as imagens.

## Registro de conclusão

A implementação no repositório pode ser encerrada após merge e testes verdes. Os itens operacionais acima continuam **não comprovados** até sua execução por quem detém as permissões. Eles estão reunidos aqui para serem conferidos durante o retorno ao teste #102, sem gerar uma falsa aprovação nem depender da memória de uma conversa.
