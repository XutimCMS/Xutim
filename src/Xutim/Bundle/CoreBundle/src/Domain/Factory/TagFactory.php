<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Domain\Model\TagTranslationInterface;
use Xutim\CoreBundle\Entity\Color;

class TagFactory
{
    public function __construct(
        private readonly string $tagClass,
        private readonly string $tagTranslationClass
    ) {
        if (!class_exists($tagClass)) {
            throw new \InvalidArgumentException(sprintf('Tag class "%s" does not exist.', $tagClass));
        }

        if (!class_exists($tagTranslationClass)) {
            throw new \InvalidArgumentException(sprintf('TagTranslation class "%s" does not exist.', $tagTranslationClass));
        }
    }

    public function create(string $name, string $slug, string $locale, Color $color, ?FileInterface $featuredImage, ?string $layout): TagInterface
    {
        /** @var TagInterface $tag */
        $tag = new ($this->tagClass)($color, $featuredImage, $layout);

        /** @var TagTranslationInterface $translation */
        $translation = new ($this->tagTranslationClass)($name, $slug, $locale, $tag);
        $tag->addTranslation($translation);

        return $tag;
    }
}
