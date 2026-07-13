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
use Xutim\CoreBundle\Form\Admin\SectionFormType;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\ArticleSectionFieldProvider;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\CollectionSectionFieldProvider;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\FileSectionFieldProvider;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\ImageSectionFieldProvider;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\SectionCollectionEntryType;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\SectionFieldProviderInterface;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\SectionFieldProviderRegistry;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\LinkSectionFieldProvider;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\MediaFolderSectionFieldProvider;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\PageSectionFieldProvider;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\PageOrArticleSectionFieldProvider;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\SnippetSectionFieldProvider;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\TagSectionFieldProvider;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\RichTextSectionFieldProvider;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\TextareaSectionFieldProvider;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\TextSectionFieldProvider;
use Xutim\CoreBundle\Form\Admin\SectionFieldProvider\UnionSectionFieldProvider;
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

    $services->instanceof(SectionFieldProviderInterface::class)
        ->tag('xutim.section_field_provider');

    $services->set(TextSectionFieldProvider::class)
        ->tag('xutim.section_field_provider');

    $services->set(TextareaSectionFieldProvider::class)
        ->tag('xutim.section_field_provider');

    $services->set(RichTextSectionFieldProvider::class)
        ->tag('xutim.section_field_provider');

    $services->set(LinkSectionFieldProvider::class)
        ->tag('xutim.section_field_provider');

    $services->set(PageSectionFieldProvider::class)
        ->arg('$pageRepository', service(PageRepository::class))
        ->tag('xutim.section_field_provider');

    $services->set(ArticleSectionFieldProvider::class)
        ->arg('$articleClass', '%xutim_core.model.article.class%')
        ->arg('$articleRepository', service(ArticleRepository::class))
        ->tag('xutim.section_field_provider');

    $services->set(PageOrArticleSectionFieldProvider::class)
        ->arg('$pageRepository', service(PageRepository::class))
        ->arg('$articleRepository', service(ArticleRepository::class))
        ->tag('xutim.section_field_provider');

    $services->set(TagSectionFieldProvider::class)
        ->arg('$tagClass', '%xutim_core.model.tag.class%')
        ->arg('$tagRepository', service(TagRepository::class))
        ->arg('$localeSwitcher', service('translation.locale_switcher'))
        ->arg('$defaultLocale', '%kernel.default_locale%')
        ->tag('xutim.section_field_provider');

    $services->set(SnippetSectionFieldProvider::class)
        ->arg('$snippetClass', '%xutim_snippet.model.snippet.class%')
        ->arg('$snippetRepository', service(SnippetRepositoryInterface::class))
        ->tag('xutim.section_field_provider');

    $services->set(ImageSectionFieldProvider::class)
        ->arg('$mediaClass', '%xutim_media.model.media.class%')
        ->arg('$mediaRepository', service(MediaRepositoryInterface::class))
        ->tag('xutim.section_field_provider');

    $services->set(FileSectionFieldProvider::class)
        ->arg('$mediaClass', '%xutim_media.model.media.class%')
        ->arg('$mediaRepository', service(MediaRepositoryInterface::class))
        ->tag('xutim.section_field_provider');

    $services->set(MediaFolderSectionFieldProvider::class)
        ->arg('$mediaFolderClass', '%xutim_media.model.media_folder.class%')
        ->arg('$mediaFolderRepository', service(MediaFolderRepositoryInterface::class))
        ->tag('xutim.section_field_provider');

    $services->set(CollectionSectionFieldProvider::class)
        ->tag('xutim.section_field_provider');

    $services->set(UnionSectionFieldProvider::class)
        ->tag('xutim.section_field_provider');

    $services->set(SectionCollectionEntryType::class)
        ->arg('$registry', service(SectionFieldProviderRegistry::class))
        ->tag('form.type');

    $services->set(SectionFieldProviderRegistry::class)
        ->arg('$providers', tagged_iterator('xutim.section_field_provider'));

    $services->set(SectionFormType::class)
        ->arg('$registry', service(SectionFieldProviderRegistry::class))
        ->tag('form.type');
};
