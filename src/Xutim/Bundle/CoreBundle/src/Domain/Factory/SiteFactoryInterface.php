<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\SiteInterface;

interface SiteFactoryInterface
{
    public function create(): SiteInterface;
}
