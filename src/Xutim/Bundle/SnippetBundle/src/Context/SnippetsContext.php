<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Context;

use Symfony\Contracts\Cache\CacheInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

class SnippetsContext
{
    public function __construct(
        private readonly CacheInterface $snippetsContextCache,
        private readonly SnippetRepositoryInterface $repo
    ) {
    }

    public function getSnippet(string $code): ?SnippetData
    {
        return $this->snippetsContextCache->get(
            $code,
            fn (): ?SnippetData => $this->repo->findByCode($code)?->toData()
        );
    }

    public function resetSnippet(string $code): void
    {
        $this->snippetsContextCache->delete($code);
    }
}
