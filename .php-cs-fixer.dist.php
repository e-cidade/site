<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['build_local', 'build_preview', 'build_production', 'vendor', 'vendor-bin']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,
        'declare_strict_types' => true,
        'braces_position' => [
            'allow_single_line_anonymous_functions' => true,
            'allow_single_line_empty_anonymous_classes' => true,
        ],
        'control_structure_braces' => true,
        'control_structure_continuation_position' => ['position' => 'same_line'],
        'statement_indentation' => true,
        'no_multiple_statements_per_line' => true,
        'method_argument_space' => [
            'attribute_placement' => 'standalone',
            'keep_multiple_spaces_after_comma' => false,
            'on_multiline' => 'ensure_fully_multiline',
        ],
    ])
    ->setFinder($finder);
