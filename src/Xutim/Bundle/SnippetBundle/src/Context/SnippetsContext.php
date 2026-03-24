<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Context;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Xutim\CoreBundle\Cache\SnippetUsageTracker;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

class SnippetsContext
{
    public function __construct(
        private readonly TagAwareCacheInterface $snippetsContextCache,
        private readonly SnippetRepositoryInterface $repo,
        private readonly SnippetUsageTracker $snippetUsageTracker
    ) {
    }

    public function getSnippet(string $code): ?SnippetData
    {
        $this->snippetUsageTracker->track($code);

        return $this->snippetsContextCache->get(
            $code,
            function (ItemInterface $item) use ($code): ?SnippetData {
                $item->tag(['snippet.' . $code]);

                return $this->repo->findByCode($code)?->toData();
            }
        );
    }

    public function resetSnippet(string $code): void
    {
        $this->snippetsContextCache->invalidateTags(['snippet.' . $code]);
    }
}
