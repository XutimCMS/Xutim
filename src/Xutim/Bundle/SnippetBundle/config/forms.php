<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Contracts\Translation\TranslatorInterface;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SnippetBundle\Form\SnippetType;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(SnippetType::class)
        ->arg('$authChecker', service(TranslatorAuthChecker::class))
        ->arg('$translator', service(TranslatorInterface::class))
        ->tag('form.type');
};
