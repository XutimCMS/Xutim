<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\SnippetInterface;

class SnippetFactory
{
    public function __construct(
        private readonly string $snippetClass
    ) {
        if (!class_exists($snippetClass)) {
            throw new \InvalidArgumentException(sprintf('Snippet class "%s" does not exist.', $snippetClass));
        }
    }

    public function create(string $code): SnippetInterface
    {
        /** @var SnippetInterface $snippet */
        $snippet = new ($this->snippetClass)($code);

        return $snippet;
    }
}
