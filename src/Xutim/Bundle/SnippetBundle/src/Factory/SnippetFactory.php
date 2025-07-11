<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Factory;

use Xutim\SnippetBundle\Domain\Factory\SnippetFactoryInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetCategory;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

class SnippetFactory implements SnippetFactoryInterface
{
    public function __construct(
        private readonly string $entityClass
    ) {
        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException(sprintf('Snippet class "%s" does not exist.', $entityClass));
        }
    }

    public function create(string $code, string $description, SnippetCategory $category): SnippetInterface
    {
        /** @var SnippetInterface $snippet */
        $snippet = new ($this->entityClass)($code, $description, $category);

        return $snippet;
    }
}
