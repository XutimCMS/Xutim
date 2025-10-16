<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\Slugger\SluggerInterface;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\BlockItemRepository;
use Xutim\CoreBundle\Repository\BlockRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\FileTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\MediaFolderRepository;
use Xutim\CoreBundle\Repository\MenuItemRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Repository\SiteRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Repository\TagTranslationRepository;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(BlockRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.block.class%')
        ->tag('doctrine.repository_service');

    $services->set(BlockItemRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.block_item.class%')
        ->tag('doctrine.repository_service');

    $services->set(TagRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.tag.class%')
        ->tag('doctrine.repository_service');

    $services->set(ContentTranslationRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.content_translation.class%')
        ->arg('$slugger', service(SluggerInterface::class))
        ->arg('$siteContext', service(SiteContext::class))
        ->tag('doctrine.repository_service');

    $services->set(LogEventRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.log_event.class%')
        ->tag('doctrine.repository_service');

    $services->set(ArticleRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.article.class%')
        ->arg('$tagEntityClass', '%xutim_core.model.tag.class%')
        ->arg('$defaultLocale', '%kernel.default_locale%')
        ->tag('doctrine.repository_service');

    $services->set(PageRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.page.class%')
        ->arg('$contentTranslationEntityClass', '%xutim_core.model.content_translation.class%')
        ->arg('$contentContext', service(ContentContext::class))
        ->tag('doctrine.repository_service');

    $services->set(MenuItemRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.menu_item.class%')
        ->tag('doctrine.repository_service');

    $services->set(FileTranslationRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.file_translation.class%')
        ->tag('doctrine.repository_service');

    $services->set(SiteRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.site.class%')
        ->tag('doctrine.repository_service');

    $services->set(TagTranslationRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.tag_translation.class%')
        ->arg('$slugger', service(SluggerInterface::class))
        ->tag('doctrine.repository_service');

    $services->set(FileRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.file.class%')
        ->tag('doctrine.repository_service');
    
    $services->set(MediaFolderRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.media_folder.class%')
        ->tag('doctrine.repository_service');
};
