<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Persistence\ManagerRegistry;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetTranslationRepositoryInterface;
use Xutim\SnippetBundle\Repository\SnippetRepository;
use Xutim\SnippetBundle\Repository\SnippetTranslationRepository;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(SnippetRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_snippet.model.snippet.class%')
        ->tag('doctrine.repository_service');

    $services->set(SnippetTranslationRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_snippet.model.snippet_translation.class%')
        ->tag('doctrine.repository_service');

    $services->alias(SnippetRepositoryInterface::class, SnippetRepository::class);
    $services->alias(SnippetTranslationRepositoryInterface::class, SnippetTranslationRepository::class);
};
