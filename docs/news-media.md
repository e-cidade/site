<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Mídia das notícias enviadas por GitHub Issues

Anexos do GitHub são aceitos como entrada editorial, mas não permanecem como dependência do site publicado.

## Fluxo

```text
anexo na Issue
   ↓
download e validação
   ↓
source/assets/images/news/<issue>/
   ↓
URL local no Markdown
```

A capa usa um nome determinístico, como `cover.png`. Imagens inline usam um nome derivado da URL remota, evitando duplicação dentro da mesma sincronização.

## Restrições

- somente HTTPS;
- somente hosts de anexos GitHub explicitamente permitidos;
- limite de 10 MB por arquivo;
- JPEG, PNG, WebP, GIF e AVIF;
- SVG é recusado, evitando conteúdo ativo controlado pelo autor.

## Remoção e substituição

Substituir a capa reutiliza o caminho `cover.<ext>` e atualiza o arquivo quando o conteúdo muda.

Imagens inline antigas não são apagadas automaticamente quando deixam de ser referenciadas. Esse comportamento é intencional: evita remoção destrutiva de mídia histórica ou compartilhada. Limpeza de órfãos deve ser uma operação separada e auditável.


## Direitos, crédito e REUSE

A mídia editorial não herda automaticamente a licença AGPL do código do site.

Para notícias novas que contenham imagens, a Issue registra:

- detentor dos direitos;
- licença ou permissão aplicável;
- crédito de exibição, quando necessário;
- confirmação de que o colaborador possui autorização para permitir a publicação.

Cada arquivo localizado em `source/assets/images/news/<issue>/` recebe um arquivo sidecar `.license` com `SPDX-FileCopyrightText` e `SPDX-License-Identifier`. Isso mantém a informação de REUSE vinculada ao arquivo concreto, em vez de aplicar uma licença genérica a toda mídia editorial.

O fluxo inicial usa `LicenseRef-eCidade-Editorial-Permission`: a permissão específica para publicação fica registrada na Issue de origem e não concede direitos adicionais além dessa permissão. Isso evita presumir que fotografias e outras mídias herdam a licença do código. O suporte a licenças públicas de mídia pode ser ampliado posteriormente, desde que o texto SPDX correspondente seja versionado e a origem/licença sejam verificáveis.
