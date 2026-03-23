<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Translation\LocaleSwitcher;
use Xutim\SnippetBundle\Context\SnippetsContext;
use Xutim\SnippetBundle\Twig\LocaleAwareSnippetExtension;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(LocaleAwareSnippetExtension::class)
        ->arg('$context', service(SnippetsContext::class))
        ->arg('$localeSwitcher', service(LocaleSwitcher::class))
        ->arg('$defaultLocale', param('kernel.default_locale'))
        ->tag('twig.extension')
    ;
};
