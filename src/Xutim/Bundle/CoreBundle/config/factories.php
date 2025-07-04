<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\CoreBundle\Domain\Factory\ArticleFactory;
use Xutim\CoreBundle\Domain\Factory\BlockFactory;
use Xutim\CoreBundle\Domain\Factory\BlockItemFactory;
use Xutim\CoreBundle\Domain\Factory\ContentTranslationFactory;
use Xutim\CoreBundle\Domain\Factory\FileFactory;
use Xutim\CoreBundle\Domain\Factory\FileTranslationFactory;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Domain\Factory\MenuItemFactory;
use Xutim\CoreBundle\Domain\Factory\PageFactory;
use Xutim\CoreBundle\Domain\Factory\SiteFactory;
use Xutim\CoreBundle\Domain\Factory\SnippetFactory;
use Xutim\CoreBundle\Domain\Factory\SnippetTranslationFactory;
use Xutim\CoreBundle\Domain\Factory\TagFactory;
use Xutim\CoreBundle\Domain\Factory\TagTranslationFactory;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(FileFactory::class)
        ->arg('$fileClass', '%xutim_core.model.file.class%')
        ->arg('$fileTranslationClass', '%xutim_core.model.file_translation.class%');

    $services->set(TagFactory::class)
        ->arg('$tagClass', '%xutim_core.model.tag.class%')
        ->arg('$tagTranslationClass', '%xutim_core.model.tag_translation.class%');

    $services->set(ArticleFactory::class)
        ->arg('$articleClass', '%xutim_core.model.article.class%')
        ->arg('$contentTranslationClass', '%xutim_core.model.content_translation.class%');

    $services->set(PageFactory::class)
        ->arg('$pageClass', '%xutim_core.model.page.class%')
        ->arg('$contentTranslationClass', '%xutim_core.model.content_translation.class%');

    $services->set(SnippetFactory::class)
        ->arg('$snippetClass', '%xutim_core.model.snippet.class%');

    $services->set(BlockFactory::class)
        ->arg('$entityClass', '%xutim_core.model.block.class%');

    $services->set(BlockItemFactory::class)
        ->arg('$entityClass', '%xutim_core.model.block_item.class%');

    $services->set(LogEventFactory::class)
        ->arg('$entityClass', '%xutim_core.model.log_event.class%');

    $services->set(MenuItemFactory::class)
        ->arg('$entityClass', '%xutim_core.model.menu_item.class%');

    $services->set(SiteFactory::class)
        ->arg('$entityClass', '%xutim_core.model.site.class%');

    $services->set(ContentTranslationFactory::class)
        ->arg('$entityClass', '%xutim_core.model.content_translation.class%');

    $services->set(FileTranslationFactory::class)
        ->arg('$entityClass', '%xutim_core.model.file_translation.class%');

    $services->set(TagTranslationFactory::class)
        ->arg('$entityClass', '%xutim_core.model.tag_translation.class%');

    $services->set(SnippetTranslationFactory::class)
        ->arg('$entityClass', '%xutim_core.model.snippet_translation.class%');
};
