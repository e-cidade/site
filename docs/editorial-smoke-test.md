<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Smoke test do fluxo editorial

Os testes unitários cobrem regras e adapters sem criar recursos reais no GitHub. Este smoke test é reservado para validar a integração entre GitHub Issues, Actions, Pull Requests, preview e deploy.

Use uma Issue explicitamente criada para teste, como #102.

## 1. Sincronização

1. a Issue deve possuir a identidade `editorial/news`;
2. salve uma alteração editorial válida;
3. confirme que a mesma branch `content/news-<issue>` é atualizada;
4. confirme que o mesmo Draft PR é reutilizado;
5. confirme que o comentário de status é atualizado, não duplicado.

## 2. Preview

1. aguarde o workflow PR Preview;
2. confirme que Build, REUSE e integridade de assets passam;
3. abra a URL publicada em `/pr-preview/pr-<número>/`;
4. confirme capa e imagens inline;
5. confirme que a Issue só anuncia a prévia como pronta depois do deploy do preview.

## 3. Validação de erro

1. torne um campo obrigatório inválido;
2. confirme que nenhum snapshot inválido é commitado;
3. confirme o estado `editorial/invalid` e a mensagem acionável;
4. corrija a Issue;
5. confirme o retorno ao ciclo normal sem criar outro PR.

## 4. Revisão e publicação

1. mova o PR de Draft para Ready for review;
2. confirme que o GitHub aplica os responsáveis definidos em `.github/CODEOWNERS`;
3. aprove formalmente o PR como code owner;
4. faça merge;
5. aguarde o deploy de produção;
6. confirme estado `published`, URL pública no comentário e fechamento automático da Issue.

## 5. Revisão posterior

1. reabra a mesma Issue publicada;
2. altere título ou conteúdo sem trocar sua identidade;
3. confirme novo ciclo de revisão da mesma notícia;
4. confirme que o slug/URL original permanece;
5. confirme presença de `updated_at` na revisão;
6. publique novamente e confirme que não foi criada uma notícia duplicada.

## 6. Automação dedicada

Quando a GitHub App editorial estiver configurada:

1. edite a Issue;
2. confirme que o commit/PR é atualizado pela identidade da App;
3. confirme que CI e preview iniciam sem `Approve workflows to run`;
4. confira que nenhuma credencial aparece no diff, `.git/config`, logs ou comentários.

O smoke test não substitui PHPUnit. Ele valida apenas integrações que dependem do comportamento real do GitHub.
