<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;

final class ContentTranslationExtendedDto
{
    /**
     * @param EditorBlock $content
     */
    public function __construct(
        private ?string $preTitle,
        private ?string $title,
        private ?string $subTitle,
        private ?string $slug,
        private array $content,
        private ?string $description,
        private ?string $locale
    ) {
    }

    public function getPreTitle(): string
    {
        return $this->preTitle ?? '';
    }

    public function getTitle(): string
    {
        Assert::stringNotEmpty($this->title);
        return $this->title;
    }

    public function getSubTitle(): string
    {
        return $this->subTitle ?? '';
    }

    public function getSlug(): string
    {
        Assert::stringNotEmpty($this->slug);
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
        return $this->description ?? '';
    }

    public function getLocale(): string
    {
        Assert::stringNotEmpty($this->locale);
        return $this->locale;
    }

    public static function fromTranslation(ContentTranslationInterface $translation): self
    {
        return new self(
            $translation->getPreTitle(),
            $translation->getTitle(),
            $translation->getSubTitle(),
            $translation->getSlug(),
            $translation->getContent(),
            $translation->getDescription(),
            $translation->getLocale()
        );
    }
}
