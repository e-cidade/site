<!--
SPDX-FileCopyrightText: 2026 e-Cidade community
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Repository settings

Recommended GitHub settings:

- description: Site comunitário do e-Cidade — software livre para gestão pública municipal integrada.
- website: https://site-ecidade.librecode.coop/ enquanto o domínio institucional `ecidade.softwarepublico.org` continuar apontando para o WordPress legado; após o cutover, trocar para https://ecidade.softwarepublico.org/
- topics: e-cidade, software-publico, software-livre, gestao-municipal, governo-digital, jigsaw, php, static-site
- Issues: enabled for site-specific work and editorial news submissions.
- Discussions: disabled; community discussion belongs in the forum at https://ecidades.popsolutions.co.
- Projects: disabled; project management belongs in the broader community governance space.

## Pull request governance

For `main`, keep pull requests as the publication boundary:

- require a pull request before merge;
- require at least one approving review;
- dismiss stale approvals when new commits are pushed;
- require the relevant CI and preview checks to pass;
- keep merge commits enabled so the branch commit history is preserved;
- do not require squash merge for editorial or technical pull requests.

For generated news PRs, the PR starts as draft. A maintainer marks it ready for review, a human approves the exact generated snapshot, and merge authorizes publication.
