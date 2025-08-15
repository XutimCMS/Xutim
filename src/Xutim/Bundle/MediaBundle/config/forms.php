<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

// use Xutim\CoreBundle\Context\Admin\ContentContext;
// use Xutim\CoreBundle\Context\SiteContext;
// use Xutim\CoreBundle\Repository\PageRepository;
// use Xutim\EventBundle\Form\Admin\EventArticleType;
// use Xutim\EventBundle\Form\Admin\EventType;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // $services->set(EventArticleType::class)
    //     ->arg('$articleClass', '%xutim_core.model.article.class%')
    //     ->tag('form.type');
    //
    // $services->set(EventType::class)
    //     ->arg('$siteContext', service(SiteContext::class))
    //     ->arg('$contentContext', service(ContentContext::class))
    //     ->arg('$pageRepository', service(PageRepository::class))
    //     ->arg('$articleClass', '%xutim_core.model.article.class%')
    //     ->tag('form.type');
};
