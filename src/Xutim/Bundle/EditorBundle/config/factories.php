<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\EditorBundle\Domain\Factory\ContentBlockFactory;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ContentBlockFactory::class)
        ->arg('$blockTypes', '%xutim_editor.block_types%');
};
