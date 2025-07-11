<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Factory;

use Xutim\SnippetBundle\Domain\Factory\SnippetTranslationFactoryInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetTranslationInterface;

class SnippetTranslationFactory implements SnippetTranslationFactoryInterface
{
    public function __construct(private readonly string $entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException(sprintf('SnippetTranslation class "%s" does not exist.', $entityClass));
        }
    }

    public function create(SnippetInterface $snippet, string $locale, string $content): SnippetTranslationInterface
    {
        /** @var SnippetTranslationInterface $trans */
        $trans = new ($this->entityClass)($snippet, $locale, $content);

        return $trans;
    }
}
