<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Routing\Dynamic;

use Symfony\Component\HttpFoundation\Request;

interface DynamicRouteResolverInterface
{
    public function resolve(string $path, Request $request): ?DynamicMatch;
}
