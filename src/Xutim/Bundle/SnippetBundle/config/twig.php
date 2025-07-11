<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\HttpFoundation\RequestStack;
use Xutim\SnippetBundle\Context\SnippetsContext;
use Xutim\SnippetBundle\Twig\LocaleAwareSnippetExtension;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(LocaleAwareSnippetExtension::class)
        ->arg('$context', service(SnippetsContext::class))
        ->arg('$requestStack', service(RequestStack::class))
        ->tag('twig.extension')
    ;
};
