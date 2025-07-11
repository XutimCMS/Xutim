<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Context;

use Xutim\SnippetBundle\Domain\Model\SnippetCategory;

readonly class SnippetData
{
    /**
     * @param array<string, string> $contents
    */
    public function __construct(
        public string $code,
        public string $description,
        public SnippetCategory $category,
        public array $contents
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
}
