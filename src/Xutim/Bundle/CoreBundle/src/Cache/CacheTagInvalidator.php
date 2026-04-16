<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Cache;

use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheTagInvalidator
{
    /** @var list<TagAwareCacheInterface> */
    private array $pools;

    public function __construct(
        TagAwareCacheInterface $blockContextCache,
        TagAwareCacheInterface $siteContextCache,
        TagAwareCacheInterface $snippetsContextCache,
        TagAwareCacheInterface $pageTreeCache,
    ) {
        $this->pools = [$blockContextCache, $siteContextCache, $snippetsContextCache, $pageTreeCache];
    }

    /**
     * @param list<string> $tags
     */
    public function invalidateTags(array $tags): void
    {
        if ($tags === []) {
            return;
        }

        foreach ($this->pools as $pool) {
            $pool->invalidateTags($tags);
        }
    }
}
