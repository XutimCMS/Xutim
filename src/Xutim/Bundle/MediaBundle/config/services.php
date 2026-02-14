<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\MessageHandler\RegenerateVariantsHandler;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaTranslationRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
use Xutim\MediaBundle\Service\BlurHashGenerator;
use Xutim\MediaBundle\Service\MediaUploader;
use Xutim\MediaBundle\Service\VariantCleaner;
use Xutim\MediaBundle\Service\VariantGenerator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(RegenerateVariantsHandler::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service(MediaVariantRepositoryInterface::class),
            service(VariantGenerator::class),
            service(VariantCleaner::class),
            service('doctrine.orm.entity_manager'),
            '%xutim_media.model.media_variant.class%',
        ])
        ->tag('messenger.message_handler');

    $services->set(MediaUploader::class)
        ->args([
            service(StorageAdapterInterface::class),
            service(MediaRepositoryInterface::class),
            service(MediaTranslationRepositoryInterface::class),
            service(BlurHashGenerator::class),
            service('messenger.default_bus'),
            '%xutim_media.model.media.class%',
            '%xutim_media.model.media_translation.class%',
        ]);
};
