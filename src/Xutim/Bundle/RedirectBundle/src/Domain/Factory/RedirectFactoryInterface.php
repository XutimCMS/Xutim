<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Domain\Factory;

use Xutim\RedirectBundle\Domain\Model\RedirectInterface;

interface RedirectFactoryInterface
{
    public function create(
        string $source,
        string $target,
        bool $permanent = false
    ): RedirectInterface;
}
