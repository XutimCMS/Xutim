<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\ContentTranslation;

use Symfony\Component\Uid\Uuid;

final readonly class MarkRecentlyOutdatedSiblingsAsSyncedCommand
{
    public function __construct(
        public ?Uuid $pageId,
        public ?Uuid $articleId,
        public string $userIdentifier,
    ) {
    }

    public function hasArticle(): bool
    {
        return $this->articleId !== null;
    }

    public function hasPage(): bool
    {
        return $this->pageId !== null;
    }
}
