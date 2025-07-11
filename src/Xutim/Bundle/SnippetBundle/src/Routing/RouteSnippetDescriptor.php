<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Routing;

class RouteSnippetDescriptor
{
    /**
     * @param array<mixed>              $defaults
     * @param array<string|\Stringable> $requirements
    */
    public function __construct(
        public readonly string $snippetKey,
        public readonly string $routeName,
        public readonly string $controller,
        public readonly array $defaults = [],
        public readonly array $requirements = [],
        public readonly ?string $host = null
    ) {
    }
}
