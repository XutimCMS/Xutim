<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Persistence\ManagerRegistry;
use Xutim\EditorBundle\Repository\ContentBlockRepository;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ContentBlockRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_editor.model.content_block.class%')
        ->tag('doctrine.repository_service');
};
