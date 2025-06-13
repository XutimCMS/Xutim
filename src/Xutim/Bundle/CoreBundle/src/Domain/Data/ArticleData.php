<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Data;

use Xutim\CoreBundle\Domain\Model\FileInterface;

final readonly class ArticleData implements ArticleDataInterface
{
    /**
     * @param EditorBlock $content
    */
    public function __construct(
        private ?string $layout,
        private string $preTitle,
        private string $title,
        private string $subTitle,
        private string $slug,
        private array $content,
        private string $description,
        private string $defaultLanguage,
        private string $userIdentifier,
        private ?FileInterface $featuredImage,
    ) {
    }

    public function getLayout(): ?string
    {
        return $this->layout;
    }

    public function getPreTitle(): string
    {
        return $this->preTitle;
    }
    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSubTitle(): string
    {
        return $this->subTitle;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return EditorBlock
    */
    public function getContent(): array
    {
        return $this->content;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function getFeaturedImage(): ?FileInterface
    {
        return $this->featuredImage;
    }
}
