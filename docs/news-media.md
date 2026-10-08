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

## Restrições técnicas

- somente HTTPS;
- somente hosts de anexos GitHub explicitamente permitidos;
- limite de 10 MB por arquivo;
- JPEG, PNG, WebP, GIF e AVIF;
- SVG é recusado, evitando conteúdo ativo controlado pelo autor.

## Contrato de direitos e proveniência

A mídia editorial não herda automaticamente a licença AGPL do código do site.

Para cada notícia com imagens, o fluxo registra um único conjunto de metadados aplicável às imagens daquela notícia:

- detentor dos direitos;
- licença ou fundamento de permissão;
- URL de origem quando a licença for pública;
- crédito quando exigido pela licença ou pelo detentor;
- declaração do colaborador de que verificou a origem e o direito de publicação.

Se uma notícia precisar combinar imagens com regimes de direitos diferentes, o modelo atual não é suficiente: a publicação deve ser ajustada editorialmente antes do merge ou o contrato precisa ser evoluído para metadata por arquivo.

### Permissão específica

`LicenseRef-eCidade-Editorial-Permission` é usada quando a autorização de publicação está registrada na própria Issue e não existe uma licença pública reutilizável.

Nesse caso:

- detentor dos direitos: obrigatório;
- origem externa da imagem: opcional;
- crédito: opcional, salvo obrigação específica registrada editorialmente;
- declaração do colaborador: obrigatória.

A LicenseRef permite ao projeto armazenar e publicar a mídia no contexto editorial registrado, sem presumir relicenciamento para terceiros.

### Licenças públicas suportadas

O fluxo suporta:

- `CC-BY-4.0`;
- `CC-BY-SA-4.0`;
- `CC0-1.0`.

Para qualquer licença pública:

- detentor dos direitos: obrigatório;
- URL de origem da mídia: obrigatória;
- declaração de verificação da licença: obrigatória.

Para `CC-BY-4.0` e `CC-BY-SA-4.0`, o crédito também é obrigatório.

A URL de origem fica versionada no front matter como `media_source_url` e pode ser exibida na página. O identificador SPDX fica no sidecar `.license` de cada arquivo localizado.

## REUSE

Cada arquivo em `source/assets/images/news/<issue>/` recebe sidecar `.license` com:

- `SPDX-FileCopyrightText`;
- `SPDX-License-Identifier`.

A metadata pertence ao arquivo concreto. O fato de a notícia e o código do site usarem AGPL não altera a licença da mídia.

## Mídia histórica

Mídia histórica sem proveniência suficiente não deve receber uma licença inventada.

Ao migrar conteúdo antigo:

1. preservar o arquivo e a referência histórica;
2. registrar metadata conhecida sem completar lacunas por suposição;
3. quando possível, localizar a publicação/origem original e regularizar copyright/licença;
4. se não houver base segura para redistribuição, tratar o caso editorialmente antes de consolidar a mídia no novo fluxo.

A regularização histórica deve ser rastreável em PR específico.

## Remoção e substituição

Substituir a capa reutiliza o caminho `cover.<ext>` e atualiza o arquivo quando o conteúdo muda.

Imagens inline antigas não são apagadas automaticamente quando deixam de ser referenciadas. Isso é intencional para preservar histórico. Limpeza de órfãos deve ser uma operação separada, auditável e baseada em referências conhecidas, nunca uma exclusão automática durante a sincronização.


## Auditoria de mídia

A manutenção de mídia usa `php scripts/audit-news-media.php`.

O comando é deliberadamente não destrutivo. Ele produz um inventário com:

- mídia referenciada por posts;
- mídia órfã, isto é, presente no repositório mas sem referência nos posts atuais;
- nova mídia editorial sem sidecar REUSE.

Arquivos órfãos são apenas reportados. A exclusão deve acontecer em Pull Request específico, após revisão de histórico e referências, para que a trilha de auditoria seja preservada.

### Acervo migrado

O acervo em `source/assets/images/migrated/**` não deve mais ser marcado como AGPL por conveniência. Enquanto copyright/licença individual não forem verificados, esses arquivos usam `LicenseRef-eCidade-Historical-Media`, que registra explicitamente o estado de direitos desconhecido sem conceder uma licença inexistente.

Quando um arquivo histórico for regularizado, a metadata genérica deve ser substituída por metadata específica e verificável.


## Evidências versionadas do acervo histórico

A auditoria diferencia **evidência de proveniência** de **prova de licença**.

`HistoricalMediaEvidenceCatalog` registra URLs onde a origem editorial ou o contexto visual foi localizado. Isso permite avançar a pesquisa sem converter uma pista em copyright/licença por inferência.

Os estados adicionais são:

- `source-located-license-pending`: existe uma fonte editorial verificável relacionada à mídia, mas não uma licença reutilizável explícita;
- `pending-evidence`: existe apenas uma pista contextual e ainda falta localizar a origem exata;
- `historical-pending`: ainda não existe evidência versionada;
- `orphan-candidate`: não há referência atual em conteúdo versionado.

Uma mídia só deve deixar o estado pendente quando copyright e fundamento de reutilização forem comprovados de forma suficiente para substituir a `LicenseRef-eCidade-Historical-Media`.
