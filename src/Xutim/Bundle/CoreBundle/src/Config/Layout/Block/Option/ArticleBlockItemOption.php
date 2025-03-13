<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Entity\BlockItem;

class ArticleBlockItemOption implements BlockItemOption
{
    public function canFullFill(BlockItem $item): bool
    {
        return $item->hasArticle() === true;
    }
}
