<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Sincronização de Issue para Pull Request

Uma Issue criada pelo formulário de notícia é a fonte editorial. O workflow `Sync news issue` materializa essa fonte em um snapshot Markdown revisável.

## Fluxo

```text
Issue válida
   ↓
content/news-<issue>
   ↓
source/_posts/<slug>.md
   ↓
Draft PR
```

O Draft PR é criado automaticamente na primeira submissão válida. Isso elimina a necessidade de labels ou comandos técnicos para o autor.

Enquanto o PR estiver aberto, edições da Issue atualizam a mesma branch e o mesmo PR.

## Identidade

- branch: `content/news-<issue-number>`;
- front matter: `github_issue: <issue-number>`;
- PR: no máximo um PR aberto por Issue;
- slug: derivado do título apenas na primeira materialização e preservado nas edições seguintes.

## Concorrência e idempotência

O workflow usa um grupo de concorrência por número de Issue e não cancela uma execução anterior. O sincronizador produz diff somente quando o snapshot realmente muda.

## Segurança editorial

O PR nasce em draft. Criar ou editar a Issue nunca publica diretamente na `main`; revisão, CI, preview e merge continuam sendo obrigatórios antes da publicação.
