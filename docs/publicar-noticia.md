<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Como publicar uma notícia

As notícias do site são arquivos Markdown. O autor da notícia não precisa conhecer HTML, CSS, JavaScript, Blade ou Jigsaw.

## O que você precisa editar

Cada notícia tem duas partes:

1. um pequeno cabeçalho entre `---`, com os dados da publicação;
2. o texto da notícia em Markdown.

Use este modelo mínimo:

```markdown
---
title: Título da notícia
description: Resumo curto, usado na listagem e nos mecanismos de busca.
date: 2026-10-07
cover_image: /assets/images/news/nome-da-imagem.jpg
cover_alt: Descrição objetiva da imagem para acessibilidade.
---
<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

Primeiro parágrafo da notícia.

## Um subtítulo

Outro parágrafo com **negrito**, *itálico* e [links](https://example.org/).

- um item;
- outro item.

![Descrição da imagem](/assets/images/news/outra-imagem.jpg)
```

O `title`, o `description` e o `date` devem ser preenchidos. A imagem de capa é recomendada, mas opcional.

O site já fornece automaticamente:

- layout da notícia;
- área onde o conteúdo será renderizado;
- autor padrão: `Comunidade e-Cidade`;
- categoria padrão: `Notícias`;
- navegação entre notícias;
- metadados de SEO e imagem social.

Não copie `extends`, `section`, `author` ou `category` para uma notícia nova, a menos que exista uma necessidade editorial específica.

## Nome do arquivo

Crie o arquivo dentro de `source/_posts/` usando apenas letras minúsculas, números e hífens.

Exemplo:

```text
source/_posts/prefeitura-adota-e-cidade.md
```

O nome do arquivo vira parte da URL da notícia. Depois de publicada, evite renomeá-lo sem necessidade.

## Imagens

Para notícias novas, coloque as imagens em:

```text
source/assets/images/news/
```

No Markdown e no cabeçalho use o caminho público:

```text
/assets/images/news/nome-da-imagem.jpg
```

Sempre preencha um texto alternativo que descreva o conteúdo relevante da imagem. Não use nomes como “imagem”, “foto” ou “banner” como texto alternativo.

## Campos opcionais

Quando a notícia reproduzir ou resumir conteúdo publicado originalmente em outro lugar, acrescente:

```yaml
source_url: https://exemplo.org/noticia-original
source_label: Nome da fonte
```

Para identificar outro autor:

```yaml
author: Nome da pessoa ou organização
```

Para colocar uma legenda na capa:

```yaml
cover_caption: Texto da legenda
```

## Markdown mais usado

```markdown
## Subtítulo

**texto em negrito**

*texto em itálico*

[texto do link](https://example.org/)

- item de lista
- outro item

1. primeiro passo
2. segundo passo

> Citação curta.

![Texto alternativo](/assets/images/news/imagem.jpg)
```

Evite HTML dentro da notícia. Se o Markdown não resolver um caso, abra uma discussão ou pull request antes de inserir marcação específica.

## Publicação pelo site do GitHub

Quem não usa Git local pode fazer todo o fluxo no navegador:

1. abra a pasta `source/_posts/` no GitHub;
2. escolha **Add file → Create new file**;
3. dê ao arquivo um nome terminado em `.md`;
4. cole o modelo acima e substitua os exemplos;
5. escreva a notícia abaixo do cabeçalho usando Markdown;
6. use a aba **Preview** do editor para revisar o Markdown;
7. proponha a alteração em uma nova branch;
8. abra o pull request;
9. confira o preview do site gerado para o pull request antes do merge.

Se houver imagem, envie primeiro o arquivo para `source/assets/images/news/` usando **Add file → Upload files**.

## Checklist editorial

Antes de pedir revisão, confira:

- título claro e específico;
- resumo que explica a notícia sem repetir apenas o título;
- data no formato `AAAA-MM-DD`;
- nome do arquivo curto e legível;
- imagem de capa com texto alternativo, quando houver;
- links funcionando;
- fonte original informada, quando aplicável;
- nenhum HTML necessário para estruturar o conteúdo;
- preview do pull request com título, imagem e formatação corretos.
