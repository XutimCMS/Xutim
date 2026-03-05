<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\ArticleBlockItemProvider;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\BlockItemProviderRegistry;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\FileBlockItemProvider;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\ImageBlockItemProvider;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\MediaFolderBlockItemProvider;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\PageBlockItemProvider;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\SnippetBlockItemProvider;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\TagBlockItemProvider;
use Xutim\CoreBundle\Form\Admin\BlockItemType;
use Xutim\CoreBundle\Form\Admin\MenuItemType;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(MenuItemType::class)
        ->arg('$pageRepository', service(PageRepository::class))
        ->arg('$articleRepository', service(ArticleRepository::class))
        ->arg('$tagRepository', service(TagRepository::class))
        ->arg('$snippetRepository', service(SnippetRepositoryInterface::class))
        ->arg('$contentContext', service(ContentContext::class))
        ->arg('$articleClass', '%xutim_core.model.article.class%')
        ->arg('$snippetClass', '%xutim_snippet.model.snippet.class%')
        ->arg('$tagClass', '%xutim_core.model.tag.class%')
        ->tag('form.type');

    $services->set(ArticleBlockItemProvider::class)
        ->arg('$articleClass', '%xutim_core.model.article.class%')
        ->tag('xutim.block_item_provider');

    $services->set(PageBlockItemProvider::class)
        ->arg('$pageRepository', service(PageRepository::class))
        ->tag('xutim.block_item_provider');

    $services->set(FileBlockItemProvider::class)
        ->arg('$mediaClass', '%xutim_media.model.media.class%')
        ->tag('xutim.block_item_provider');

    $services->set(ImageBlockItemProvider::class)
        ->arg('$mediaClass', '%xutim_media.model.media.class%')
        ->tag('xutim.block_item_provider');

    $services->set(SnippetBlockItemProvider::class)
        ->arg('$snippetClass', '%xutim_snippet.model.snippet.class%')
        ->tag('xutim.block_item_provider');

    $services->set(TagBlockItemProvider::class)
        ->arg('$tagClass', '%xutim_core.model.tag.class%')
        ->tag('xutim.block_item_provider');

    $services->set(MediaFolderBlockItemProvider::class)
        ->arg('$mediaFolderClass', '%xutim_media.model.media_folder.class%')
        ->tag('xutim.block_item_provider');

    $services->set(BlockItemProviderRegistry::class)
        ->arg('$providers', tagged_iterator('xutim.block_item_provider'));

    $services->set(BlockItemType::class)
        ->arg('$registry', service(BlockItemProviderRegistry::class))
        ->tag('form.type');
};
