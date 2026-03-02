<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\LocaleSwitcher;
use Xutim\SnippetBundle\Context\SnippetsContext;
use Xutim\SnippetBundle\Twig\LocaleAwareSnippetExtension;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(LocaleAwareSnippetExtension::class)
        ->arg('$context', service(SnippetsContext::class))
        ->arg('$requestStack', service(RequestStack::class))
        ->arg('$localeSwitcher', service(LocaleSwitcher::class))
        ->tag('twig.extension')
    ;
};
