<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Infra\Layout;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class LayoutCacheWarmer implements CacheWarmerInterface
{
    public function __construct(
        private readonly LayoutLoader $loader,
    ) {
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $this->loader->loadAllLayouts(); // Triggers re-caching

        return [];
    }

    public function isOptional(): bool
    {
        return true;
    }
}
