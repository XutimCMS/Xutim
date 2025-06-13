<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Data\ArticleDataInterface;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;

interface ArticleFactoryInterface
{
    public function create(ArticleDataInterface $data): ArticleInterface;
}
