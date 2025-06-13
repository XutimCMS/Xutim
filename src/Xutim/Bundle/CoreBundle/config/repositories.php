<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\Slugger\SluggerInterface;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Factory\ResetPasswordRequestFactory;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\BlockItemRepository;
use Xutim\CoreBundle\Repository\BlockRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\FileTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\MenuItemRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Repository\ResetPasswordRequestRepository;
use Xutim\CoreBundle\Repository\SiteRepository;
use Xutim\CoreBundle\Repository\SnippetRepository;
use Xutim\CoreBundle\Repository\SnippetTranslationRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Repository\TagTranslationRepository;
use Xutim\CoreBundle\Repository\UserRepository;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->set(UserRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.user.class%')
        ->tag('doctrine.repository_service');

    $services->set(BlockRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.block.class%')
        ->tag('doctrine.repository_service');

    $services->set(BlockItemRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.block_item.class%')
        ->tag('doctrine.repository_service');

    $services->set(SnippetTranslationRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.snippet_translation.class%')
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
        ->tag('doctrine.repository_service');

    $services->set(PageRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.page.class%')
        ->arg('$contentContext', service(ContentContext::class))
        ->tag('doctrine.repository_service');

    $services->set(SnippetRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.snippet.class%')
        ->tag('doctrine.repository_service');

    $services->set(ResetPasswordRequestRepository::class)
        ->arg('$registry', service(ManagerRegistry::class))
        ->arg('$entityClass', '%xutim_core.model.reset_password_request.class%')
        ->arg('$factory', service(ResetPasswordRequestFactory::class))
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
};
