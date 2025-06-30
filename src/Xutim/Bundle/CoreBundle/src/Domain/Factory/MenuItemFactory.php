<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\MenuItemInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\SnippetInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;

class MenuItemFactory
{
    public function __construct(private readonly string $entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException(sprintf('MenuItem class "%s" does not exist.', $entityClass));
        }
    }

    public function create(
        ?MenuItemInterface $parent,
        bool $hasLink,
        ?PageInterface $page,
        ?ArticleInterface $article,
        ?TagInterface $tag,
        ?PageInterface $overwritePage,
        ?SnippetInterface $snippetAnchor
    ): MenuItemInterface {
        /** @var MenuItemInterface $item */
        $item = new ($this->entityClass)(
            $parent,
            $hasLink,
            $page,
            $article,
            $tag,
            $overwritePage,
            $snippetAnchor
        );

        return $item;
    }
}
