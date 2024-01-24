<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedTraitsFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withConfiguredRule(
        ClassDefinitionFixer::class,
        [
            'space_before_parenthesis' => true,
        ],
    )
    ->withFileExtensions(['php'])
    ->withPaths(
        [
            __DIR__ . '/src',
            __DIR__ . '/tests',
        ],
    )
    ->withPhpCsFixerSets(perCS20: true)
    ->withPreparedSets(
        arrays: true,
        cleanCode: true,
        comments:true,
        docblocks: true,
        namespaces: true,
        psr12: true,
        strict: true
    )
    ->withRules(
        [
            NoUnusedImportsFixer::class,
            OrderedTraitsFixer::class,
        ]
    );
