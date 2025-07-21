<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Infra\Routing;

use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\RouterInterface;

readonly class RedirectRouteService
{
    public function __construct(
        private string $redirectVersionPath,
        private string $cacheDir,
        private RouterInterface $router
    ) {
    }

    public function resetRedirectRoutesCache(): void
    {
        // Restart the redirect_routes router cache. See
        // CustomRouteLoader for more information
        file_put_contents($this->redirectVersionPath, microtime());

        if ($this->router instanceof WarmableInterface) {
            $this->router->warmUp($this->cacheDir);
        }
    }
}
