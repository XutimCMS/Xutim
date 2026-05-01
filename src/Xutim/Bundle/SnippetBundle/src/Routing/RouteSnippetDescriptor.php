<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Routing;

final readonly class RouteSnippetDescriptor
{
    public function __construct(
        public string $snippetKey,
        public string $routeName,
        public string $controller
    ) {
    }
}
