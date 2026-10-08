<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final class HistoricalMediaEvidenceCatalog
{
    /**
     * Evidence is intentionally weaker than a license assertion.
     *
     * @return array<string, array{
     *   evidence_urls:list<string>,
     *   evidence_note:string,
     *   rights_status:string
     * }>
     */
    public function all(): array
    {
        return [
            '/assets/images/migrated/ambiente-demonstracao.png' => [
                'evidence_urls' => [
                    'https://ecidade.softwarepublico.org/software-e-cidade-ganha-ambiente-de-demonstracao/',
                    'https://www.youtube.com/watch?v=WoeoxEe8TFk',
                ],
                'evidence_note' => 'A página histórica do e-Cidade e a gravação da demonstração foram localizadas, mas não há licença reutilizável explícita para a imagem.',
                'rights_status' => 'source-located-license-pending',
            ],
            '/assets/images/migrated/aula-inaugural.png' => [
                'evidence_urls' => [
                    'https://artecult.com/aula-inaugural-do-e-cidade-faz-historia/',
                ],
                'evidence_note' => 'A página editorial original contém a cobertura visual do evento, sem licença reutilizável explícita.',
                'rights_status' => 'source-located-license-pending',
            ],
            '/assets/images/migrated/aula-inaugural-turma.jpeg' => [
                'evidence_urls' => [
                    'https://artecult.com/aula-inaugural-do-e-cidade-faz-historia/',
                ],
                'evidence_note' => 'A página editorial original contém registro da turma, sem licença reutilizável explícita.',
                'rights_status' => 'source-located-license-pending',
            ],
            '/assets/images/migrated/bens-publicos-digitais.png' => [
                'evidence_urls' => [
                    'https://artecult.com/brasil-ainda-pode-ser-lider-mundial-em-bens-publicos-digitais/',
                    'https://c3sl.pages.c3sl.ufpr.br/blog/inedito-brasil-centro-bens-publicos-digitais-lancamento-novembro/',
                ],
                'evidence_note' => 'A mídia aparece no contexto de matérias sobre bens públicos digitais; nenhuma das páginas consultadas declara licença reutilizável para a imagem.',
                'rights_status' => 'source-located-license-pending',
            ],
            '/assets/images/migrated/centro-bens-publicos-digitais.png' => [
                'evidence_urls' => [
                    'https://c3sl.pages.c3sl.ufpr.br/blog/inedito-brasil-centro-bens-publicos-digitais-lancamento-novembro/',
                ],
                'evidence_note' => 'A página do C3SL/UFPR é a origem editorial conhecida, mas não foi encontrada licença explícita para a imagem.',
                'rights_status' => 'source-located-license-pending',
            ],
            '/assets/images/migrated/cristiano-furtado.jpg' => [
                'evidence_urls' => [
                    'https://artecult.com/o-software-e-cidade-aprimora-seu-modelo-de-governanca-em-rede/',
                ],
                'evidence_note' => 'A imagem é usada na matéria de governança em rede, sem licença reutilizável explícita.',
                'rights_status' => 'source-located-license-pending',
            ],
            '/assets/images/migrated/wellington-carvalho.jpg' => [
                'evidence_urls' => [
                    'https://artecult.com/o-software-e-cidade-aprimora-seu-modelo-de-governanca-em-rede/',
                ],
                'evidence_note' => 'A imagem é usada na matéria de governança em rede, sem licença reutilizável explícita.',
                'rights_status' => 'source-located-license-pending',
            ],
            '/assets/images/migrated/governanca-em-rede.png' => [
                'evidence_urls' => [
                    'https://artecult.com/o-software-e-cidade-aprimora-seu-modelo-de-governanca-em-rede/',
                ],
                'evidence_note' => 'A página editorial original é conhecida, mas não declara licença reutilizável para a arte.',
                'rights_status' => 'source-located-license-pending',
            ],
            '/assets/images/migrated/evandro-schaulet.png' => [
                'evidence_urls' => [
                    'https://artecult.com/e-cidade-amplia-governanca-e-passa-a-contar-com-representantes-da-academia-no-comite-gestor/',
                ],
                'evidence_note' => 'A matéria identifica Evandro Schaulet junto à imagem, mas não declara licença reutilizável.',
                'rights_status' => 'source-located-license-pending',
            ],
            '/assets/images/migrated/igor-oliveira.png' => [
                'evidence_urls' => [
                    'https://artecult.com/e-cidade-amplia-governanca-e-passa-a-contar-com-representantes-da-academia-no-comite-gestor/',
                ],
                'evidence_note' => 'A matéria identifica Igor Oliveira junto à imagem, mas não declara licença reutilizável.',
                'rights_status' => 'source-located-license-pending',
            ],
            '/assets/images/migrated/governanca-academia.png' => [
                'evidence_urls' => [
                    'https://artecult.com/e-cidade-amplia-governanca-e-passa-a-contar-com-representantes-da-academia-no-comite-gestor/',
                ],
                'evidence_note' => 'A arte aparece na matéria de governança acadêmica, sem licença reutilizável explícita.',
                'rights_status' => 'source-located-license-pending',
            ],
            '/assets/images/migrated/mexico-software-publico.jpeg' => [
                'evidence_urls' => [
                    'https://artecult.com/brasil-ainda-pode-ser-lider-mundial-em-bens-publicos-digitais/',
                ],
                'evidence_note' => 'A matéria identifica o registro como sendo de 2014 e relacionado ao governo do México, mas não informa autor nem licença da fotografia.',
                'rights_status' => 'source-located-license-pending',
            ],
            '/assets/images/migrated/pagina-comunidade.png' => [
                'evidence_urls' => [
                    'https://artecult.com/pagina-do-e-cidade-lancada-pela-comunidade/',
                ],
                'evidence_note' => 'A página editorial original registra o lançamento do site, sem licença reutilizável explícita para a arte.',
                'rights_status' => 'source-located-license-pending',
            ],
            '/assets/images/migrated/pagina-comunidade-registro.jpg' => [
                'evidence_urls' => [
                    'https://artecult.com/pagina-do-e-cidade-lancada-pela-comunidade/',
                ],
                'evidence_note' => 'A página editorial original contém registro visual do lançamento, sem licença reutilizável explícita.',
                'rights_status' => 'source-located-license-pending',
            ],
            '/assets/images/migrated/sertao-digital.png' => [
                'evidence_urls' => [
                    'https://artecult.com/sertao-digital-impulsiona-modelo-de-cidades-inteligentes-na-paraiba/',
                ],
                'evidence_note' => 'A página editorial original do projeto Sertão Digital foi localizada, sem licença reutilizável explícita para a imagem.',
                'rights_status' => 'source-located-license-pending',
            ],
            '/assets/images/migrated/sertao-digital-registro.jpg' => [
                'evidence_urls' => [
                    'https://artecult.com/sertao-digital-impulsiona-modelo-de-cidades-inteligentes-na-paraiba/',
                ],
                'evidence_note' => 'A página editorial original do projeto Sertão Digital foi localizada, sem licença reutilizável explícita para o registro.',
                'rights_status' => 'source-located-license-pending',
            ],
        ];
    }

    /**
     * @return array{evidence_urls:list<string>,evidence_note:string,rights_status:string}|null
     */
    public function for(string $publicPath): ?array
    {
        return $this->all()[$publicPath] ?? null;
    }
}
