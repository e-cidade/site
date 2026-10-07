<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Contribuindo com o site do e-Cidade

Contribuições são bem-vindas para conteúdo, documentação, acessibilidade, design, correções e evolução técnica do site.

Antes de começar uma mudança relevante, procure uma issue existente. Quando o trabalho ainda não estiver representado, abra uma issue para registrar o contexto e permitir alinhamento com a comunidade.

## Publicando uma notícia

Para propor uma notícia, use o formulário **Publicar notícia** na criação de uma nova Issue.

Basta preencher título, resumo, data e texto. Imagem, autoria específica e fonte original são opcionais. Não é necessário conhecer Git, Markdown, HTML, CSS, JavaScript ou os templates do site.

A Issue é usada para acompanhar a revisão editorial. Comentários servem para conversar sobre ajustes e não entram automaticamente no texto publicado.

O fluxo técnico de branch, Pull Request, preview e publicação é responsabilidade dos mantenedores.

## Fluxo básico para alterações técnicas

1. Crie uma branch a partir de `main`.
2. Faça alterações pequenas e revisáveis.
3. Adicione ou atualize testes quando houver comportamento testável.
4. Execute os checks relacionados à mudança.
5. Faça commits com DCO (`git commit -s`).
6. Abra um pull request descrevendo problema, solução e impacto.

Pull requests que alteram a interface recebem um preview automático para revisão.

## Adicionando um prestador de serviços

A página de prestadores é gerada pela collection `providers`. Para incluir uma empresa, não edite o template HTML.

1. Adicione a logo em `source/assets/images/providers/`.
2. Crie um arquivo Markdown em `source/_providers/`, usando um dos arquivos existentes como exemplo.
3. Preencha os campos:
   - `name`: nome exibido;
   - `website`: URL completa;
   - `website_label`: endereço curto exibido no card;
   - `logo`: caminho público da imagem, começando por `/assets/images/providers/`;
   - `logo_alt`: texto alternativo da logo;
   - `group`: `credenciada` ou `outra`;
   - `order`: ordem numérica dentro da seção.
4. Abra o pull request com apenas o arquivo da collection e a logo.

O template `source/prestadores-de-servico.blade.php` lê a collection automaticamente.

## Padrões técnicos

As regras de arquitetura, testes, qualidade, SPDX/REUSE, toolchain, Docker e assinatura de commits estão em [AGENTS.md](../AGENTS.md).

## Assuntos gerais do e-Cidade

Questões sobre o ecossistema e-Cidade como um todo devem ser tratadas no repositório comunitário `e-cidade/e-cidade`. Issues deste repositório devem permanecer focadas no site.
