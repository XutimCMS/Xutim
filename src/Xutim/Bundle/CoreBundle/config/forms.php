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
use Xutim\CoreBundle\Form\Admin\LayoutFormType;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\ArticleLayoutFieldProvider;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\CollectionLayoutFieldProvider;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\FileLayoutFieldProvider;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\ImageLayoutFieldProvider;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\LayoutCollectionEntryType;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\LayoutFieldProviderInterface;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\LayoutFieldProviderRegistry;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\LinkLayoutFieldProvider;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\MediaFolderLayoutFieldProvider;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\PageLayoutFieldProvider;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\PageOrArticleLayoutFieldProvider;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\SnippetLayoutFieldProvider;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\TagLayoutFieldProvider;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\RichTextLayoutFieldProvider;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\TextareaLayoutFieldProvider;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\TextLayoutFieldProvider;
use Xutim\CoreBundle\Form\Admin\LayoutFieldProvider\UnionLayoutFieldProvider;
use Xutim\CoreBundle\Form\Admin\MenuItemType;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
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

    $services->instanceof(LayoutFieldProviderInterface::class)
        ->tag('xutim.layout_field_provider');

    $services->set(TextLayoutFieldProvider::class)
        ->tag('xutim.layout_field_provider');

    $services->set(TextareaLayoutFieldProvider::class)
        ->tag('xutim.layout_field_provider');

    $services->set(RichTextLayoutFieldProvider::class)
        ->tag('xutim.layout_field_provider');

    $services->set(LinkLayoutFieldProvider::class)
        ->tag('xutim.layout_field_provider');

    $services->set(PageLayoutFieldProvider::class)
        ->arg('$pageRepository', service(PageRepository::class))
        ->tag('xutim.layout_field_provider');

    $services->set(ArticleLayoutFieldProvider::class)
        ->arg('$articleClass', '%xutim_core.model.article.class%')
        ->arg('$articleRepository', service(ArticleRepository::class))
        ->tag('xutim.layout_field_provider');

    $services->set(PageOrArticleLayoutFieldProvider::class)
        ->arg('$pageRepository', service(PageRepository::class))
        ->arg('$articleRepository', service(ArticleRepository::class))
        ->tag('xutim.layout_field_provider');

    $services->set(TagLayoutFieldProvider::class)
        ->arg('$tagClass', '%xutim_core.model.tag.class%')
        ->arg('$tagRepository', service(TagRepository::class))
        ->arg('$localeSwitcher', service('translation.locale_switcher'))
        ->arg('$defaultLocale', '%kernel.default_locale%')
        ->tag('xutim.layout_field_provider');

    $services->set(SnippetLayoutFieldProvider::class)
        ->arg('$snippetClass', '%xutim_snippet.model.snippet.class%')
        ->arg('$snippetRepository', service(SnippetRepositoryInterface::class))
        ->tag('xutim.layout_field_provider');

    $services->set(ImageLayoutFieldProvider::class)
        ->arg('$mediaClass', '%xutim_media.model.media.class%')
        ->arg('$mediaRepository', service(MediaRepositoryInterface::class))
        ->tag('xutim.layout_field_provider');

    $services->set(FileLayoutFieldProvider::class)
        ->arg('$mediaClass', '%xutim_media.model.media.class%')
        ->arg('$mediaRepository', service(MediaRepositoryInterface::class))
        ->tag('xutim.layout_field_provider');

    $services->set(MediaFolderLayoutFieldProvider::class)
        ->arg('$mediaFolderClass', '%xutim_media.model.media_folder.class%')
        ->arg('$mediaFolderRepository', service(MediaFolderRepositoryInterface::class))
        ->tag('xutim.layout_field_provider');

    $services->set(CollectionLayoutFieldProvider::class)
        ->tag('xutim.layout_field_provider');

    $services->set(UnionLayoutFieldProvider::class)
        ->tag('xutim.layout_field_provider');

    $services->set(LayoutCollectionEntryType::class)
        ->arg('$registry', service(LayoutFieldProviderRegistry::class))
        ->tag('form.type');

    $services->set(LayoutFieldProviderRegistry::class)
        ->arg('$providers', tagged_iterator('xutim.layout_field_provider'));

    $services->set(LayoutFormType::class)
        ->arg('$registry', service(LayoutFieldProviderRegistry::class))
        ->tag('form.type');
};
