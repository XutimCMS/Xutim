<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Routing;

use Xutim\CoreBundle\Action\Public\SearchAction;
use Xutim\CoreBundle\Action\Public\ShowNewsAction;

class RouteSnippetRegistry
{
    /**
     * @return array<RouteSnippetDescriptor>
     */
    public static function all(): array
    {
        return [
            new RouteSnippetDescriptor(
                snippetKey: 'route-news',
                routeName: 'news',
                controller: ShowNewsAction::class
            ),
            new RouteSnippetDescriptor(
                snippetKey: 'route-search',
                routeName: 'search',
                controller: SearchAction::class
            ),
        ];
    }
}
