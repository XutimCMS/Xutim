<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

final readonly class CreateArticleFormData
{
    /**
     * @param EditorBlock $content
     */
    public function __construct(
        public ?string $layout,
        public ?string $preTitle,
        public ?string $title,
        public ?string $subTitle,
        public ?string $slug,
        public ?array $content,
        public ?string $description,
        public ?string $locale,
        public ?Uuid $featuredImageId
    ) {
    }

    public function hasFeaturedImage(): bool
    {
        return $this->featuredImageId !== null;
    }

    public function getLayout(): string
    {
        Assert::string($this->layout);
        return $this->layout;
    }

    public function getPreTitle(): string
    {
        Assert::string($this->preTitle);
        return $this->preTitle;
    }

    public function getTitle(): string
    {
        Assert::string($this->title);
        return $this->title;
    }

    public function getSubTitle(): string
    {
        Assert::string($this->subTitle);
        return $this->subTitle;
    }

    public function getSlug(): string
    {
        Assert::string($this->slug);
        return $this->slug;
    }

    /**
     * @return EditorBlock
    */
    public function getContent(): array
    {
        Assert::isArray($this->content);
        return $this->content;
    }

    public function getDescription(): string
    {
        Assert::string($this->description);
        return $this->description;
    }

    public function getLocale(): string
    {
        Assert::string($this->locale);
        return $this->locale;
    }

    public function getFeaturedImageId(): ?Uuid
    {
        return $this->featuredImageId;
    }
}
