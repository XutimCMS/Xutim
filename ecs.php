<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return ECSConfig::configure()
    ->withParallel()
    ->withPaths([
        __DIR__ . '/src/Xutim/Bundle/AnalyticsBundle/src',
        __DIR__ . '/src/Xutim/Bundle/CoreBundle/src',
        __DIR__ . '/src/Xutim/Bundle/EventBundle/src',
        __DIR__ . '/src/Xutim/Bundle/MediaBundle/src',
        __DIR__ . '/src/Xutim/Bundle/RedirectBundle/src',
        __DIR__ . '/src/Xutim/Bundle/SecurityBundle/src',
        __DIR__ . '/src/Xutim/Bundle/SnippetBundle/src',
        __DIR__ . '/src/Xutim/Bundle/CoreBundle/tests',
    ])
    ->withSets([
        SetList::CLEAN_CODE,
        SetList::PSR_12,
    ])
    ->withRules([
        PhpdocAlignFixer::class,
        MethodChainingIndentationFixer::class,
        StrictComparisonFixer::class,
        StrictParamFixer::class,
        DeclareStrictTypesFixer::class,
    ])
    ->withSkip([
        MethodChainingIndentationFixer::class => [
            '*/DependencyInjection/Configuration.php',
        ],
    ])
;
