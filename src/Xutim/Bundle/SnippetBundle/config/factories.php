<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\SnippetBundle\Domain\Factory\SnippetFactoryInterface;
use Xutim\SnippetBundle\Domain\Factory\SnippetTranslationFactoryInterface;
use Xutim\SnippetBundle\Factory\SnippetFactory;
use Xutim\SnippetBundle\Factory\SnippetTranslationFactory;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->set(SnippetFactory::class)
        ->arg('$entityClass', '%xutim_snippet.model.snippet.class%');

    $services->set(SnippetTranslationFactory::class)
        ->arg('$entityClass', '%xutim_snippet.model.snippet_translation.class%');

    $services->alias(SnippetFactoryInterface::class, SnippetFactory::class);
    $services->alias(SnippetTranslationFactoryInterface::class, SnippetTranslationFactory::class);
};
