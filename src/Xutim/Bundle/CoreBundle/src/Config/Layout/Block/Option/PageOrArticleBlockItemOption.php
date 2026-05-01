<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

/**
 * Reference to either a page or an article, rendered in the admin as a
 * single Tom Select with both content types grouped. Stored as
 * `['type' => 'page'|'article', 'value' => '<uuid>']`.
 */
readonly class PageOrArticleBlockItemOption implements BlockItemOption
{
    public function canFullFill(BlockItemInterface $item): bool
    {
        return $item->hasPage() || $item->hasArticle();
    }

    public function getName(): string
    {
        return 'Page or article';
    }

    public function isTranslatable(): bool
    {
        return false;
    }

    public function getDescription(): ?string
    {
        return 'Reference to a page or an article';
    }
}
