<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Routing\ContentTranslationRouteLoader;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(ContentTranslationRouteLoader::class)
        ->arg('$snippetRepo', service(SnippetRepositoryInterface::class))
        ->arg('$siteContext', service(SiteContext::class))
        ->arg('$snippetVersionPath', '%snippet_routes_version_file%')
        ->arg('$env', '%kernel.environment%')
        ->tag('routing.loader')
    ;
};
