<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Form;

use Webmozart\Assert\Assert;
use Xutim\SnippetBundle\Domain\Model\SnippetCategory;

readonly class SnippetFormData
{
    /**
     * @param array<string, string> $contents
    */
    public function __construct(
        private ?string $code,
        private ?string $description,
        private ?SnippetCategory $category,
        private array $contents
    ) {
    }

    public function hasTranslation(string $locale): bool
    {
        return array_key_exists($locale, $this->contents);
    }

    public function getTranslation(string $locale): string
    {
        return $this->contents[$locale];
    }

    public function getCode(): string
    {
        Assert::notNull($this->code);

        return $this->code;
    }

    /**
     * @return array<string, string>
    */
    public function getContents(): array
    {
        return $this->contents;
    }

    public function getDescription(): string
    {
        Assert::notNull($this->description);

        return $this->description;
    }

    public function getCategory(): SnippetCategory
    {
        Assert::notNull($this->category);

        return $this->category;
    }
}
