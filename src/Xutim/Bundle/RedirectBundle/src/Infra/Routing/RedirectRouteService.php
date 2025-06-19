<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Infra\Routing;

class RedirectRouteService
{
    public function __construct(private readonly string $redirectVersionPath)
    {
    }

    public function resetRedirectRoutesCache(): void
    {
        // Restart the redirect_routes router cache. See
        // CustomRouteLoader for more information
        file_put_contents($this->redirectVersionPath, microtime());
    }
}
