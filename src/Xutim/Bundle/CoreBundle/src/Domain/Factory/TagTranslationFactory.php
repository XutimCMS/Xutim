<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Domain\Model\TagTranslationInterface;

class TagTranslationFactory
{
    public function __construct(private readonly string $entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException(sprintf('TagTranslation class "%s" does not exist.', $entityClass));
        }
    }

    public function create(string $name, string $slug, string $locale, TagInterface $tag): TagTranslationInterface
    {
        /** @var TagTranslationInterface $trans */
        $trans = new ($this->entityClass)($name, $slug, $locale, $tag);

        return $trans;
    }
}
