<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\CoreBundle\Context\SiteContext;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SnippetBundle\Form\SnippetType;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(SnippetType::class)
        ->arg('$context', service(SiteContext::class))
        ->arg('$authChecker', service(TranslatorAuthChecker::class))
        ->tag('form.type');
};
