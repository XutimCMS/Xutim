<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\RedirectBundle\Form\RedirectType;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(RedirectType::class)
        ->tag('form.type');
};
