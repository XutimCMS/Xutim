<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Persistence\ManagerRegistry;
use Xutim\MediaBundle\Infra\Doctrine\ORM\MediaFolderRepository;
use Xutim\MediaBundle\Infra\Doctrine\ORM\MediaRepository;
use Xutim\MediaBundle\Infra\Doctrine\ORM\MediaTranslationRepository;
use Xutim\MediaBundle\Infra\Doctrine\ORM\MediaVariantRepository;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaTranslationRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(MediaRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_media.model.media.class%')
        ->tag('doctrine.repository_service');
    $services->alias(MediaRepositoryInterface::class, MediaRepository::class);

    $services->set(MediaVariantRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_media.model.media_variant.class%')
        ->tag('doctrine.repository_service');
    $services->alias(MediaVariantRepositoryInterface::class, MediaVariantRepository::class);

    $services->set(MediaFolderRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_media.model.media_folder.class%')
        ->tag('doctrine.repository_service');
    $services->alias(MediaFolderRepositoryInterface::class, MediaFolderRepository::class);

    $services->set(MediaTranslationRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_media.model.media_translation.class%')
        ->tag('doctrine.repository_service');
    $services->alias(MediaTranslationRepositoryInterface::class, MediaTranslationRepository::class);
};
