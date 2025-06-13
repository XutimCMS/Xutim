<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

readonly class SnippetDto
{
    /**
     * @param array<string, string> $contents
    */
    public function __construct(
        public string $code,
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
