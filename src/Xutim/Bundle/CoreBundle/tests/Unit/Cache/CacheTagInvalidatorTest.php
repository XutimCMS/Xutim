<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Cache;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Xutim\CoreBundle\Cache\CacheTagInvalidator;

final class CacheTagInvalidatorTest extends TestCase
{
    /** @var TagAwareCacheInterface&MockObject */
    private TagAwareCacheInterface $blockPool;
    /** @var TagAwareCacheInterface&MockObject */
    private TagAwareCacheInterface $sitePool;
    /** @var TagAwareCacheInterface&MockObject */
    private TagAwareCacheInterface $snippetPool;
    private CacheTagInvalidator $invalidator;

    protected function setUp(): void
    {
        $this->blockPool = $this->createMock(TagAwareCacheInterface::class);
        $this->sitePool = $this->createMock(TagAwareCacheInterface::class);
        $this->snippetPool = $this->createMock(TagAwareCacheInterface::class);

        $this->invalidator = new CacheTagInvalidator(
            $this->blockPool,
            $this->sitePool,
            $this->snippetPool
        );
    }

    public function test_invalidate_tags_forwards_to_all_pools(): void
    {
        $tags = ['article.1', 'tag.2'];

        $this->blockPool->expects(self::once())->method('invalidateTags')->with($tags);
        $this->sitePool->expects(self::once())->method('invalidateTags')->with($tags);
        $this->snippetPool->expects(self::once())->method('invalidateTags')->with($tags);

        $this->invalidator->invalidateTags($tags);
    }

    public function test_empty_tags_skips_invalidation(): void
    {
        $this->blockPool->expects(self::never())->method('invalidateTags');
        $this->sitePool->expects(self::never())->method('invalidateTags');
        $this->snippetPool->expects(self::never())->method('invalidateTags');

        $this->invalidator->invalidateTags([]);
    }
}
