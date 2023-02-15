<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalInternalClassFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    // run and fix, one by one
    $ecsConfig->import(SetList::ARRAY);
    $ecsConfig->import(SetList::DOCBLOCK);
    $ecsConfig->import(SetList::PSR_12);

    $ecsConfig->ruleWithConfiguration(ArraySyntaxFixer::class, [
        'syntax' => 'short'
    ]);
    $ecsConfig->rule(NoUnusedImportsFixer::class);
    $ecsConfig->rule(DeclareStrictTypesFixer::class);
    $ecsConfig->ruleWithConfiguration(FinalInternalClassFixer::class, [
        'annotation_exclude' => ['@not-fix'],
        'annotation_include' => [],
        'consider_absent_docblock_as_internal_class' => \true
    ]);



};
