<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use Xutim\CoreBundle\Domain\Data\ArticleDataInterface;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;

class ArticleFactory
{
    public function __construct(
        private readonly string $articleClass,
        private readonly string $contentTranslationClass
    ) {
        if (!class_exists($articleClass)) {
            throw new \InvalidArgumentException(sprintf('Article class "%s" does not exist.', $articleClass));
        }

        if (!class_exists($contentTranslationClass)) {
            throw new \InvalidArgumentException(sprintf('ContentTranslation class "%s" does not exist.', $contentTranslationClass));
        }
    }

    public function create(ArticleDataInterface $data): ArticleInterface
    {
        /** @var ArticleInterface $article */
        $article = new ($this->articleClass)($data->getLayout(), $data->getTranslationLocales(), new ArrayCollection(), $data->getFeaturedImage());
        $article->changeAllTranslationLocales($data->hasAllTranslationLocales());
        /** @var ContentTranslationInterface $translation */
        $translation = new ($this->contentTranslationClass)(
            $data->getPreTitle(),
            $data->getTitle(),
            $data->getSubTitle(),
            $data->getSlug(),
            $data->getContent(),
            $data->getDefaultLanguage(),
            $data->getDescription(),
            null,
            $article
        );
        $article->addTranslation($translation);

        return $article;
    }
}
