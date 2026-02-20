<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\CoreBundle\Context\Admin\ContentContext;
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

    $services->set(BlockItemType::class)
        ->arg('$articleClass', '%xutim_core.model.article.class%')
        ->arg('$mediaClass', '%xutim_media.model.media.class%')
        ->arg('$snippetClass', '%xutim_snippet.model.snippet.class%')
        ->arg('$tagClass', '%xutim_core.model.tag.class%')
        ->arg('$mediaFolderClass', '%xutim_media.model.media_folder.class%')
        ->arg('$pageRepository', service(PageRepository::class))
        ->tag('form.type');

};
