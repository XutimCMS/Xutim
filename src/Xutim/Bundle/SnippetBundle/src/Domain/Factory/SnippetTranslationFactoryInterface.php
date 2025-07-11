<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Domain\Factory;

use Xutim\SnippetBundle\Domain\Model\SnippetInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetTranslationInterface;

interface SnippetTranslationFactoryInterface
{
    public function create(SnippetInterface $snippet, string $locale, string $content): SnippetTranslationInterface;
}
