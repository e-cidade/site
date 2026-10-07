<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Camada de conteúdo de notícias

A camada editorial normaliza diferentes fontes para um único modelo `NewsEntry`.

```text
GitHubIssueNewsSource ─┐
                      ├─> NewsEntry -> NewsEntryValidator -> NewsMarkdownWriter
WordPressNewsSource ───┘
```

Os templates do Jigsaw consomem apenas o Markdown produzido pelo writer e não precisam conhecer a fonte original.

## Identidade e slug

- `externalId` identifica de forma estável a origem editorial;
- para GitHub, o número da Issue faz parte dessa identidade;
- `slug` é apenas o caminho público;
- o primeiro slug pode ser derivado do título;
- depois da primeira publicação, o slug persistido deve ser reutilizado mesmo que o título da Issue mude.

## Idempotência

`NewsMarkdownWriter::render()` é determinístico: a mesma entrada produz os mesmos bytes. Isso permite que workflows detectem corretamente quando uma edição editorial realmente alterou o conteúdo publicado.

## Migração WordPress

`WordPressNewsSource` aceita a forma normalizada já usada pelo sincronizador atual. A troca do sincronizador para esta content layer será feita na etapa de migração, evitando alterar o comportamento WordPress antes de o novo fluxo GitHub estar comprovado.
