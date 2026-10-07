<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Aprovação e encerramento do ciclo editorial

A aprovação formal de uma notícia ocorre no Pull Request gerado a partir da Issue.

```text
Draft PR
   ↓
Ready for review
   ↓
Approve
   ↓
merge em main
   ↓
Deploy GitHub Pages
   ↓
comentário com URL
   ↓
Issue fechada
```

O merge não fecha a Issue. O fechamento só acontece depois que o workflow de produção conclui build, testes e deploy.

O deploy identifica notícias pelo head da PR (`content/news-<issue>`) associada ao commit publicado. Em seguida resolve a URL pelo `github_issue` persistido no front matter.

O comentário final contém um marcador oculto por commit para que reexecuções do mesmo deploy não dupliquem a mensagem.

Reabrir a Issue inicia um novo ciclo editorial. Uma atualização posterior gera outro PR e, quando publicada, produz um novo comentário de publicação antes de fechar novamente a mesma Issue.
