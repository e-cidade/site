<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Trust boundary do fluxo editorial

O fluxo editorial recebe conteúdo público de GitHub Issues e executa operações privilegiadas no repositório. A regra é separar dados não confiáveis de efeitos externos.

## Entrada não confiável

São tratados como dados não confiáveis:

- título e corpo da Issue;
- links e Markdown inseridos pelo autor;
- anexos e URLs de mídia;
- texto alternativo, créditos e demais metadados editoriais;
- payloads de eventos recebidos do GitHub.

Esses valores podem influenciar o conteúdo publicado, mas não devem definir comandos, nomes arbitrários de branch, caminhos fora das áreas editoriais ou permissões do workflow.

## Normalização e validação

Antes da escrita:

1. `EditorialIssueEventHandler` valida a forma básica do payload;
2. `EditorialIssueDetector` decide se a Issue pertence ao fluxo;
3. `NewsIssueContract` normaliza e valida os campos editoriais;
4. `NewsMediaLocalizer` restringe mídia a HTTPS, hosts permitidos, tipos suportados e limite de tamanho;
5. `NewsIssueSynchronizer` escreve somente o snapshot editorial.

O número da Issue determina a branch `content/news-<número>`. O título fornecido pelo usuário não é usado como nome de branch nem como comando Git.

## Efeitos privilegiados

Todos os efeitos Git/GitHub passam por `EditorialGateway`.

A implementação de produção, `GhEditorialGateway`:

- chama processos por `proc_open()` com array de argumentos, sem montar linha de shell;
- mantém o código da aplicação no checkout confiável;
- manipula conteúdo em worktree editorial isolado;
- limita restauração de arquivos às áreas `source/_posts/`, `source/assets/images/news/` e `LICENSES/`;
- protege atualização de branch existente com `--force-with-lease` vinculado ao SHA remoto observado;
- envia comentários/status como argumentos de API, não como shell.

## Permissões por workflow

### Sync news issue

- `contents: write`: criar/atualizar branch editorial;
- `issues: write`: labels e comentário de status;
- `pull-requests: write`: criar/fechar PR editorial.

### News editorial review state

- `contents: read`: carregar a aplicação;
- `issues: write`: atualizar estado/status.

A seleção de reviewer não é uma permissão da automação: ela é responsabilidade nativa do GitHub por meio de `.github/CODEOWNERS`.

### Deploy GitHub Pages

- `contents: write`: publicação no branch de Pages;
- `issues: write`: marcar/fechar Issue publicada;
- `pull-requests: read`: resolver o PR associado ao commit publicado.

### Setup editorial repository metadata

- `contents: read`: carregar a aplicação;
- `issues: write`: provisionar labels editoriais.

Uma identidade dedicada de automação deve receber somente o subconjunto necessário dessas permissões.

## Regras de logging

- tokens e segredos não entram em mensagens editoriais;
- exceções podem registrar o comando executado, mas não valores de ambiente;
- conteúdo editorial não deve ser promovido a segredo nem impresso desnecessariamente em logs;
- mensagens destinadas ao autor devem vir de validações controladas pela aplicação.

## Testabilidade

Regras editoriais usam `FakeEditorialGateway`. O adapter de processo usa `RecordingProcessRunner` nos testes para verificar os argumentos exatos sem executar Git ou GitHub CLI.

Qualquer nova operação privilegiada deve ser adicionada à interface do gateway e coberta por teste antes de ser usada por workflow.
