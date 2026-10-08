<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Inventário de mídia histórica

Este inventário acompanha a regularização de `source/assets/images/migrated/**`.

Ele pode ser reproduzido com:

```bash
php scripts/audit-news-media.php --markdown
```

Estados:

- `historical-pending`: mídia histórica referenciada, ainda coberta pela LicenseRef de estado histórico;
- `orphan-candidate`: arquivo sem referência nos posts atuais; não implica autorização para exclusão;
- `managed`: mídia do fluxo novo com metadata REUSE por arquivo;
- `missing-reuse-sidecar`: erro de metadata do fluxo novo.

A URL da matéria de origem é evidência de proveniência editorial, não prova de licença da imagem.

O inventário atual possui 16 arquivos históricos referenciados e nenhum órfão conhecido. A pesquisa de copyright/licença individual continua em #147.
