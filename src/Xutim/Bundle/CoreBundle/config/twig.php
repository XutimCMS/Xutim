<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\NewsContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Context\TagsContext;

return static function (ContainerConfigurator $container): void {
    $container->extension('twig', [
        'globals' => [
            'xutim_core_filter_sets' => '%xutim_core.filter_sets%',
            'xutim_core_admin_filter_sets' => '%xutim_core.admin_filter_sets%',
            'site_context' => service(SiteContext::class),
            'content_context' => service(ContentContext::class),
            'news_context' => service(NewsContext::class),
            'tags_context' => service(TagsContext::class),
        ],
    ]);
};
