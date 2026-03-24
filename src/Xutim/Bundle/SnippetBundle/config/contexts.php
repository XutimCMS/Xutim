<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Xutim\CoreBundle\Cache\SnippetUsageTracker;
use Xutim\SnippetBundle\Context\SnippetsContext;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(SnippetsContext::class)
        ->arg('$snippetsContextCache', service('snippets_context.cache'))
        ->arg('$repo', service(SnippetRepositoryInterface::class))
        ->arg('$snippetUsageTracker', service(SnippetUsageTracker::class))
    ;
};
