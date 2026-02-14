<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\MediaBundle\Console\CleanOrphanedVariantsCommand;
use Xutim\MediaBundle\Console\RegenerateVariantsCommand;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
use Xutim\MediaBundle\Service\PresetRegistry;
use Xutim\MediaBundle\Service\VariantCleaner;
use Xutim\MediaBundle\Service\VariantGenerator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(RegenerateVariantsCommand::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service(MediaVariantRepositoryInterface::class),
            service(VariantGenerator::class),
            service(VariantCleaner::class),
            service(PresetRegistry::class),
            service('doctrine.orm.entity_manager'),
            '%xutim_media.model.media_variant.class%',
        ])
        ->tag('console.command');

    $services->set(CleanOrphanedVariantsCommand::class)
        ->args([
            service(MediaVariantRepositoryInterface::class),
            service(StorageAdapterInterface::class),
            '%xutim_media.storage.public_dir%',
            '%xutim_media.storage.media_path%',
        ])
        ->tag('console.command');
};
