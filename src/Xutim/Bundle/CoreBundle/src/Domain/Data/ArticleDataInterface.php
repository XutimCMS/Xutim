<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Data;

use Xutim\MediaBundle\Domain\Model\MediaInterface;

interface ArticleDataInterface
{
    public function getLayout(): ?string;
    public function getPreTitle(): string;
    public function getTitle(): string;
    public function getSubTitle(): string;
    public function getSlug(): string;
    /** @return EditorBlock */
    public function getContent(): array;
    public function getDescription(): string;
    public function getDefaultLanguage(): string;
    public function getUserIdentifier(): string;
    public function getFeaturedImage(): ?MediaInterface;
}
