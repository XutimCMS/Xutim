<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;

class ContentTranslationFactory
{
    public function __construct(private readonly string $entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException(sprintf('ContentTranslation class "%s" does not exist.', $entityClass));
        }
    }

    /**
     * @param EditorBlock $content
     */
    public function create(
        string $preTitle,
        string $title,
        string $subTitle,
        string $slug,
        array $content,
        string $locale,
        string $description,
        ?PageInterface $page,
        ?ArticleInterface $article
    ): ContentTranslationInterface {
        /** @var ContentTranslationInterface $item */
        $item = new ($this->entityClass)(
            $preTitle,
            $title,
            $subTitle,
            $slug,
            $content,
            $locale,
            $description,
            $page,
            $article
        );

        return $item;
    }
}
