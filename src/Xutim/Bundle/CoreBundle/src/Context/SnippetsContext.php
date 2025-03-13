<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Context;

use Symfony\Contracts\Cache\CacheInterface;
use Xutim\CoreBundle\Form\Admin\Dto\SnippetDto;
use Xutim\CoreBundle\Repository\SnippetRepository;

class SnippetsContext
{
    public function __construct(
        private readonly CacheInterface $snippetsContextCache,
        private readonly SnippetRepository $repo
    ) {
    }

    public function getSnippet(string $code): ?SnippetDto
    {
        return $this->snippetsContextCache->get(
            $code,
            fn (): ?SnippetDto => $this->repo->findOneBy(['code' => $code])?->toDto()
        );
    }

    public function resetSnippet(string $code): void
    {
        $this->snippetsContextCache->delete($code);
    }
}
