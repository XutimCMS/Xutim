<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\BlockInterface;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

class BlockItemFactory
{
    public function __construct(private readonly string $entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException(sprintf('BlockItem class "%s" does not exist.', $entityClass));
        }
    }

    /**
     * @param array<string, mixed> $extra
     */
    public function create(
        BlockInterface $block,
        ?PageInterface $page = null,
        ?ArticleInterface $article = null,
        ?MediaInterface $file = null,
        ?SnippetInterface $snippet = null,
        ?TagInterface $tag = null,
        ?MediaFolderInterface $mediaFolder = null,
        ?string $text = null,
        ?string $link = null,
        ?string $fileDescription = null,
        array $extra = [],
    ): BlockItemInterface {
        /** @var BlockItemInterface $item */
        $item = new ($this->entityClass)(
            $block,
            $page,
            $article,
            $file,
            $snippet,
            $tag,
            $mediaFolder,
            $text,
            $link,
            $fileDescription,
            $extra,
        );

        return $item;
    }
}
