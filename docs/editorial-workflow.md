<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Operação do fluxo editorial

Este documento descreve o ciclo editorial do ponto de vista de quem envia uma notícia, de quem revisa e de quem mantém a automação.

## Visão geral

```text
Issue Form
   ↓
Issue editorial
   ↓
validação + snapshot versionado
   ↓
Draft Pull Request
   ↓
CI + preview
   ↓
Ready for review + CODEOWNERS
   ↓
Pull Request Review aprovado
   ↓
merge em main
   ↓
deploy
   ↓
URL publicada + fechamento da Issue
```

A Issue é a fonte editorial. O Pull Request é a versão exata que será revisada e publicada. O Git é o registro versionado.

## Para quem envia uma notícia

1. Abra **Publicar notícia** no GitHub.
2. Preencha resumo, data da notícia e texto.
3. Se houver imagens, informe os dados de direitos/proveniência solicitados pelo formulário.
4. Salve a Issue.
5. Acompanhe o comentário **Status editorial**.

O autor não precisa criar branch, Markdown ou Pull Request.

### O comentário de status

A automação mantém um único comentário e o atualiza durante o ciclo. Ele pode informar:

- erro de validação;
- Pull Request;
- preview disponível;
- revisão pendente;
- aprovação;
- publicação;
- URL pública.

Comentários humanos permanecem separados e nunca são incorporados automaticamente à notícia.

## Estados editoriais

| Estado | Significado |
| --- | --- |
| `editorial/draft` | snapshot criado/atualizado, ainda em rascunho |
| `editorial/invalid` | a Issue precisa de correção antes de gerar novo snapshot |
| `editorial/review` | versão pronta para revisão humana |
| `editorial/ready` | Pull Request Review aprovado |
| `editorial/published` | versão publicada com sucesso |
| `editorial/discarded` | proposta encerrada antes da publicação |

A label `editorial/news` identifica a Issue como pertencente ao fluxo e não é um estado.

## Validação e sincronização

Ao abrir, editar ou reabrir uma Issue editorial:

1. a aplicação valida o contrato;
2. mídia remota é localizada quando necessário;
3. a branch `content/news-<issue>` é preparada em worktree isolado;
4. o Markdown e a mídia são atualizados;
5. o mesmo Draft PR é reutilizado enquanto estiver aberto;
6. o comentário de status é atualizado.

Uma Issue inválida não produz um novo snapshot. O erro aparece no status e uma nova edição tenta novamente.

## Preview e CI

Build, REUSE e PR Preview continuam obrigatórios para conteúdo editorial.

PRs que alteram somente `source/_posts/**` e `source/assets/images/news/**` usam CI seletiva: os checks de código mantêm seus nomes estáveis, mas não instalam nem executam toolchains sem relação com a alteração.

A URL de preview só deve ser anunciada como pronta depois do workflow de preview concluir. Se o preview falhar, o status não deve apresentar uma URL como se a revisão atual estivesse disponível.

## Revisão

Quando o Draft PR é marcado **Ready for review**, o GitHub usa `.github/CODEOWNERS` para identificar os responsáveis editoriais.

A aprovação formal é um **Pull Request Review**. Labels e comentários não substituem essa aprovação.

A política recomendada de `main` exige:

- um approval;
- review de code owner;
- checks obrigatórios;
- dismissal de approval após novos commits;
- conversas resolvidas antes do merge.

A configuração administrativa está detalhada em [Governança editorial](editorial-governance.md).

## Publicação

O fechamento manual da Issue não publica a notícia.

A publicação é causada por:

```text
Review aprovado
    +
checks verdes
    +
merge em main
    +
deploy concluído
```

Depois do deploy, a automação:

1. resolve qual notícia foi publicada;
2. atualiza o estado para `editorial/published`;
3. grava a URL pública no comentário de status;
4. fecha a Issue como concluída.

## Descarte

Fechar uma Issue ainda não publicada representa descarte.

O fluxo:

- fecha o PR editorial sem merge;
- remove a branch editorial;
- aplica `editorial/discarded`;
- não dispara publicação.

Esse comportamento foi validado em integração real com #139/#140.

## Revisão de notícia já publicada

Reabrir uma Issue publicada representa uma nova revisão da mesma notícia.

A identidade continua sendo o número da Issue. O synchronizer preserva o slug existente por padrão, registra `updated_at` e gera novo ciclo de revisão antes de substituir a versão publicada.

A `Data da notícia` é a data editorial original, **não** um agendamento ou o horário do deploy. Depois da primeira publicação, uma revisão mantém a data do Markdown já publicado mesmo se o formulário da Issue trouxer outra data; `updated_at` registra a edição posterior. Antes da primeira publicação, correções na data editorial continuam permitidas. Se o snapshot de uma notícia já publicada não for localizado, o sincronizador interrompe a revisão em vez de criar um novo slug ou perder a data original. Uma mudança intencional e excepcional da data histórica exige revisão explícita do conteúdo versionado.

## Automação e credenciais

A arquitetura suporta uma GitHub App dedicada. Ela é necessária para que atualizações feitas pela automação possam iniciar CI/preview sem a intervenção `Approve workflows to run`.

A configuração e as permissões mínimas estão em [Identidade de automação editorial](editorial-automation.md).

## Mídia

Direitos, proveniência, crédito, licenças públicas, REUSE e tratamento de acervo histórico estão em [Mídia das notícias](news-media.md).

## Arquitetura e segurança

- workflows são adaptadores;
- regras de negócio ficam em PHP testável;
- efeitos Git/GitHub passam por `EditorialGateway`;
- branches editoriais são manipuladas em worktree isolado;
- entrada pública é tratada como não confiável;
- comandos usam argumentos estruturados, não interpolação de shell.

Detalhes estão em [Trust boundary do fluxo editorial](editorial-security.md).

Para verificar os requisitos que dependem de administrador do GitHub e os critérios de aceite da publicação real, consulte [Preparação operacional e critérios de aceite](editorial-release-readiness.md). Não considere a governança aplicada apenas pela presença do CODEOWNERS.

## Verificação de integração

Testes unitários não criam recursos reais no GitHub. Para validar integrações nativas do GitHub, use o roteiro em [Smoke test do fluxo editorial](editorial-smoke-test.md).
