<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Message\Command\Page\CreatePageCommand;

class PageFactory
{
    public function __construct(
        private readonly string $pageClass,
        private readonly string $contentTranslationClass,
    ) {
        if (!class_exists($pageClass)) {
            throw new \InvalidArgumentException(sprintf('Page class "%s" does not exist.', $pageClass));
        }

        if (!class_exists($contentTranslationClass)) {
            throw new \InvalidArgumentException(sprintf('ContentTranslation class "%s" does not exist.', $contentTranslationClass));
        }
    }

    public function create(CreatePageCommand $data, ?FileInterface $featuredImage, ?PageInterface $parent): PageInterface
    {
        /** @var PageInterface $page */
        $page = new ($this->pageClass)(
            $data->layout,
            $data->color,
            $data->locales,
            $parent,
            $featuredImage
        );

        /** @var ContentTranslationInterface $translation */
        $translation = new ($this->contentTranslationClass)(
            $data->preTitle,
            $data->title,
            $data->subTitle,
            $data->slug,
            $data->content,
            $data->defaultLanguage,
            $data->description,
            $page,
            null
        );
        $page->addTranslation($translation);

        return $page;
    }
}
