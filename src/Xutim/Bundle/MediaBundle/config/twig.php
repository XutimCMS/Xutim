<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
use Xutim\MediaBundle\Service\PresetRegistry;
use Xutim\MediaBundle\Service\VariantPathResolver;
use Xutim\MediaBundle\Twig\Extension\MediaImageExtension;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(MediaImageExtension::class)
        ->args([
            service(PresetRegistry::class),
            service(VariantPathResolver::class),
            service(MediaVariantRepositoryInterface::class),
            service(MediaRepositoryInterface::class),
            service(StorageAdapterInterface::class),
        ])
        ->tag('twig.extension');
};
