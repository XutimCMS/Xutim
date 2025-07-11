<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Domain\Factory;

use Xutim\SnippetBundle\Domain\Model\SnippetCategory;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

interface SnippetFactoryInterface
{
    public function create(string $code, string $description, SnippetCategory $category): SnippetInterface;
}
