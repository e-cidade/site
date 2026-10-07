<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Contrato editorial das notícias

A interface editorial do site é uma GitHub Issue criada pelo formulário `.github/ISSUE_TEMPLATE/news.yml`.

O autor não precisa conhecer Git, Jigsaw, front matter ou a estrutura do repositório. A Issue é a unidade editorial; os comentários são usados para revisão e discussão e não integram automaticamente o texto publicado.

## Campos

| Campo | Origem | Regra |
| --- | --- | --- |
| título | título da Issue | obrigatório |
| resumo | `Resumo` | obrigatório |
| data | `Data da publicação` | obrigatória no formato `AAAA-MM-DD` |
| autor | `Autor` | opcional; padrão `Comunidade e-Cidade` |
| conteúdo | `Texto da notícia` | obrigatório |
| imagem de capa | `Imagem de capa` | opcional |
| texto alternativo | `Texto alternativo da imagem` | obrigatório quando houver imagem |
| fonte | `Fonte original` | opcional, mas deve acompanhar URL |
| URL da fonte | `URL da fonte` | opcional, mas deve acompanhar fonte |

O número da Issue é a identidade editorial. O título e o futuro slug podem mudar sem alterar essa identidade.

O parser usa os títulos das seções geradas pelo Issue Form como contrato estável. Campos vazios gerados pelo GitHub como `_No response_` são tratados como ausência de valor.

## Issue Type

GitHub Issue Forms suportam a chave `type`, mas o tipo precisa existir previamente na organização. Quando a organização e-Cidade possuir o tipo `Notícia`, o formulário deve ser configurado com `type: Notícia`. O fluxo não depende dessa configuração para funcionar.

## Fluxo para o autor

```text
Nova notícia → preencher formulário → enviar → responder comentários de revisão → receber URL publicada
```

Branch, pull request, preview, Markdown gerado e deploy pertencem ao fluxo de manutenção e permanecem invisíveis para o autor comum.
