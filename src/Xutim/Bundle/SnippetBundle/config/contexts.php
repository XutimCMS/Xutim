<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Contracts\Cache\CacheInterface;
use Xutim\SnippetBundle\Context\SnippetsContext;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(SnippetsContext::class)
        ->arg('$snippetsContextCache', service(CacheInterface::class))
        ->arg('$repo', service(SnippetRepositoryInterface::class))
        ->tag('doctrine.repository_service');
};
