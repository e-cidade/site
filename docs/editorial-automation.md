<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Identidade de automação editorial

O fluxo de sincronização suporta uma GitHub App dedicada para que commits e atualizações de Pull Requests editoriais sejam produzidos por uma identidade de automação instalada no repositório, em vez de depender exclusivamente de `GITHUB_TOKEN`.

## Por que usar GitHub App

A App resolve duas necessidades distintas:

- uma identidade auditável para branches, commits e PRs editoriais;
- eventos produzidos pela automação podem iniciar a CI/preview sem o comportamento restritivo associado a PRs gerados por alguns tokens de Actions.

A aprovação humana para merge continua separada e deve ser imposta pela governança de `main`.

## Permissões mínimas da App

Instale a App somente no repositório `e-cidade/site` e conceda:

- **Contents: Read and write** — branch e commits editoriais;
- **Issues: Read and write** — labels e comentário único de status;
- **Pull requests: Read and write** — criação/fechamento do PR editorial e leitura de estado;
- **Metadata: Read-only** — permissão implícita/básica do GitHub App.

A App não precisa de Actions, Administration, Secrets ou Members.

## Configuração do repositório

Depois de criar e instalar a App:

1. crie a repository variable `EDITORIAL_APP_ID` com o App ID;
2. gere uma private key da App;
3. salve o conteúdo PEM como repository secret `EDITORIAL_APP_PRIVATE_KEY`.

O workflow `Sync news issue` detecta esses dois valores. Quando ambos existem, cria um installation token limitado ao repositório e solicita explicitamente apenas:

- `contents: write`;
- `issues: write`;
- `pull-requests: write`.

Enquanto a App ainda não estiver configurada, o workflow mantém fallback para `GITHUB_TOKEN` para não interromper o fluxo existente. A issue #121 só deve ser considerada concluída depois de validar uma edição real de notícia usando a App.

## Credenciais Git

`actions/checkout` usa `persist-credentials: false`.

O token não é gravado no repositório nem no `.git/config`. O job configura `gh auth setup-git`, e os subprocessos Git executados pela aplicação reutilizam a credencial efêmera disponível em `GH_TOKEN`.

O token de instalação expira e é revogado pelo action ao final do job.

## Identidade dos commits

Quando a App está ativa, o workflow resolve o usuário bot da instalação e passa para a aplicação:

- `EDITORIAL_GIT_USER_NAME=<app-slug>[bot]`;
- `EDITORIAL_GIT_USER_EMAIL=<bot-id>+<app-slug>[bot]@users.noreply.github.com`.

`GhEditorialGateway` usa esses valores somente como identidade Git. Sem App, preserva a identidade `github-actions[bot]`.

## Validação operacional

Após configurar a App:

1. editar a Issue de teste #102;
2. confirmar que a branch `content/news-102` é atualizada pela App;
3. confirmar que PR #104 recebe o novo commit;
4. confirmar que CI e PR Preview iniciam sem `Approve workflows to run`;
5. conferir que o token não aparece em logs ou configuração Git;
6. somente então remover o fallback para `GITHUB_TOKEN` e reduzir as permissões nativas do workflow em follow-up.
