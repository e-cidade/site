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
