<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Camada de conteúdo de notícias

A camada editorial normaliza a GitHub Issue para um único modelo `NewsEntry`.

```text
GitHub Issue -> GitHubIssueNewsSource -> NewsEntry -> NewsEntryValidator -> NewsMarkdownWriter
```

Os templates do Jigsaw consomem apenas o Markdown produzido pelo writer e não precisam conhecer a origem anterior do conteúdo.

## Identidade e slug

- `externalId` identifica de forma estável a origem editorial;
- o número da Issue é a identidade editorial publicada;
- `slug` é o caminho público e não a identidade;
- o primeiro slug pode ser derivado do título;
- importações legadas podem declarar `<!-- e-cidade-slug:... -->` para preservar a URL existente;
- depois da primeira publicação, o slug persistido é reutilizado mesmo que o título da Issue mude.

## Idempotência

`NewsMarkdownWriter::render()` é determinístico: a mesma entrada produz os mesmos bytes. Isso permite que workflows detectem corretamente quando uma edição editorial realmente alterou o conteúdo publicado.

## WordPress legado

O WordPress não escreve mais diretamente em `source/_posts`.

`WordPressIssueExporter` lê apenas a REST API pública e produz o mesmo contrato de Issue usado pelo formulário editorial. O workflow de importação identifica posts por `e-cidade-wordpress-id`, associa notícias históricas por título, usa um hash para evitar reprocessamento e dispara explicitamente o workflow `Sync news issue`.

O dispatch explícito evita depender de um evento encadeado a partir de operações realizadas com `GITHUB_TOKEN`.

Notícias publicadas antes do cutover mantêm seus slugs e arquivos atuais, mas recebem `github_issue` para vincular o snapshot à Issue histórica correspondente.
