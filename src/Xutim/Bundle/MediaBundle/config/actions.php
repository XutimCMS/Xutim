<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\MediaBundle\Action\Admin\EditCopyrightAction;
use Xutim\MediaBundle\Action\Admin\EditInnerNameAction;
use Xutim\MediaBundle\Action\Admin\EditFocalPointAction;
use Xutim\MediaBundle\Action\Admin\JsonListAllFilesAction;
use Xutim\MediaBundle\Action\Admin\JsonListFilesAction;
use Xutim\MediaBundle\Action\Admin\JsonListImagesAction;
use Xutim\MediaBundle\Action\Admin\JsonShowFileAction;
use Xutim\MediaBundle\Action\Admin\ListMediaAction;
use Xutim\MediaBundle\Action\Admin\CreateFolderAction;
use Xutim\MediaBundle\Action\Admin\DeleteFolderAction;
use Xutim\MediaBundle\Action\Admin\DeleteMediaAction;
use Xutim\MediaBundle\Action\Admin\EditFolderAction;
use Xutim\MediaBundle\Action\Admin\EditMediaAction;
use Xutim\MediaBundle\Action\Admin\MoveMediaAction;
use Xutim\MediaBundle\Action\Admin\MoveMediaToFolderAction;
use Xutim\MediaBundle\Action\Admin\PresetPreviewAction;
use Xutim\MediaBundle\Action\Admin\RegenerateVariantsAction;
use Xutim\MediaBundle\Action\Admin\ReplaceMediaAction;
use Xutim\MediaBundle\Action\Admin\ShowMediaTranslationAction;
use Xutim\MediaBundle\Action\Admin\UpdateFocalPointAction;
use Xutim\MediaBundle\Action\Admin\UploadMediaAction;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaTranslationRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
use Xutim\MediaBundle\Service\MediaUploader;
use Xutim\MediaBundle\Service\PresetRegistry;
use Xutim\MediaBundle\Service\VariantCleaner;
use Xutim\MediaBundle\Service\VariantPathResolver;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SecurityBundle\Service\UserStorage;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(UpdateFocalPointAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service('doctrine.orm.entity_manager'),
            service('security.authorization_checker'),
            service('messenger.default_bus'),
            service(LogEventFactory::class),
            service(LogEventRepository::class),
            service(UserStorage::class),
            '%xutim_media.model.media.class%',
        ])
        ->tag('controller.service_arguments');

    $services->set(RegenerateVariantsAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service('security.authorization_checker'),
            service('messenger.default_bus'),
        ])
        ->tag('controller.service_arguments');

    $services->set(PresetPreviewAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service(MediaVariantRepositoryInterface::class),
            service(PresetRegistry::class),
            service(VariantPathResolver::class),
            service('security.authorization_checker'),
            service('twig'),
        ])
        ->tag('controller.service_arguments');

    $services->set(EditMediaAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service(MediaTranslationRepositoryInterface::class),
            service(MediaVariantRepositoryInterface::class),
            service(PresetRegistry::class),
            service(VariantPathResolver::class),
            service(ContentContext::class),
            service(TranslatorAuthChecker::class),
            service(LogEventFactory::class),
            service(LogEventRepository::class),
            service(UserStorage::class),
            '%xutim_media.model.media_translation.class%',
            '%xutim_media.model.media.class%',
            service('form.factory'),
            service('twig'),
            service('router'),
            service('security.authorization_checker'),
        ])
        ->tag('controller.service_arguments');

    $services->set(UploadMediaAction::class)
        ->args([
            service(MediaUploader::class),
            service(MediaFolderRepositoryInterface::class),
            service(LogEventFactory::class),
            service(LogEventRepository::class),
            service(UserStorage::class),
            service('form.factory'),
            service('twig'),
            service(AdminUrlGenerator::class),
            service('security.authorization_checker'),
            '%xutim_media.model.media.class%',
        ])
        ->tag('controller.service_arguments');

    $services->set(DeleteMediaAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service(MediaTranslationRepositoryInterface::class),
            service(MediaVariantRepositoryInterface::class),
            service(VariantCleaner::class),
            service(StorageAdapterInterface::class),
            service(LogEventFactory::class),
            service(LogEventRepository::class),
            service(UserStorage::class),
            service('form.factory'),
            service('twig'),
            service(AdminUrlGenerator::class),
            service('security.authorization_checker'),
            '%xutim_media.model.media.class%',
        ])
        ->tag('controller.service_arguments');

    $services->set(CreateFolderAction::class)
        ->args([
            service(MediaFolderRepositoryInterface::class),
            service(LogEventFactory::class),
            service(LogEventRepository::class),
            service(UserStorage::class),
            service('form.factory'),
            service('twig'),
            service(AdminUrlGenerator::class),
            service('security.authorization_checker'),
            '%xutim_media.model.media_folder.class%',
        ])
        ->tag('controller.service_arguments');

    $services->set(EditFolderAction::class)
        ->args([
            service(MediaFolderRepositoryInterface::class),
            service(LogEventFactory::class),
            service(LogEventRepository::class),
            service(UserStorage::class),
            service('form.factory'),
            service('twig'),
            service(AdminUrlGenerator::class),
            service('security.authorization_checker'),
            '%xutim_media.model.media_folder.class%',
        ])
        ->tag('controller.service_arguments');

    $services->set(DeleteFolderAction::class)
        ->args([
            service(MediaFolderRepositoryInterface::class),
            service(LogEventFactory::class),
            service(LogEventRepository::class),
            service(UserStorage::class),
            service('form.factory'),
            service('twig'),
            service(AdminUrlGenerator::class),
            service('security.authorization_checker'),
            '%xutim_media.model.media_folder.class%',
        ])
        ->tag('controller.service_arguments');

    $services->set(MoveMediaAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service(MediaFolderRepositoryInterface::class),
            service(LogEventFactory::class),
            service(LogEventRepository::class),
            service(UserStorage::class),
            service('twig'),
            service(AdminUrlGenerator::class),
            service('security.authorization_checker'),
            '%xutim_media.model.media.class%',
        ])
        ->tag('controller.service_arguments');

    $services->set(MoveMediaToFolderAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service(MediaFolderRepositoryInterface::class),
            service(LogEventFactory::class),
            service(LogEventRepository::class),
            service(UserStorage::class),
            '%xutim_media.model.media.class%',
        ])
        ->tag('controller.service_arguments');

    $services->set(ListMediaAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service(MediaFolderRepositoryInterface::class),
            service('twig'),
        ])
        ->tag('controller.service_arguments');

    $services->set(ReplaceMediaAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service(MediaUploader::class),
            service(LogEventFactory::class),
            service(LogEventRepository::class),
            service(UserStorage::class),
            service('form.factory'),
            service('twig'),
            service(AdminUrlGenerator::class),
            service('security.authorization_checker'),
            '%xutim_media.model.media.class%',
        ])
        ->tag('controller.service_arguments');

    $services->set(EditCopyrightAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service(LogEventFactory::class),
            service(LogEventRepository::class),
            service(UserStorage::class),
            service('form.factory'),
            service('twig'),
            service(AdminUrlGenerator::class),
            service('security.authorization_checker'),
            '%xutim_media.model.media.class%',
        ])
        ->tag('controller.service_arguments');

    $services->set(EditInnerNameAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service(LogEventFactory::class),
            service(LogEventRepository::class),
            service(UserStorage::class),
            service('form.factory'),
            service('twig'),
            service(AdminUrlGenerator::class),
            service('security.authorization_checker'),
            '%xutim_media.model.media.class%',
        ])
        ->tag('controller.service_arguments');

    $services->set(EditFocalPointAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service('twig'),
            service(AdminUrlGenerator::class),
            service('security.authorization_checker'),
        ])
        ->tag('controller.service_arguments');

    $services->set(JsonListAllFilesAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
        ])
        ->tag('controller.service_arguments');

    $services->set(JsonListImagesAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service(MediaFolderRepositoryInterface::class),
            service(MediaVariantRepositoryInterface::class),
            service(VariantPathResolver::class),
            service(StorageAdapterInterface::class),
        ])
        ->tag('controller.service_arguments');

    $services->set(JsonListFilesAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service(MediaFolderRepositoryInterface::class),
            service(StorageAdapterInterface::class),
        ])
        ->tag('controller.service_arguments');

    $services->set(JsonShowFileAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service(StorageAdapterInterface::class),
        ])
        ->tag('controller.service_arguments');

    $services->set(ShowMediaTranslationAction::class)
        ->args([
            service(MediaRepositoryInterface::class),
            service(StorageAdapterInterface::class),
        ])
        ->tag('controller.service_arguments');
};
