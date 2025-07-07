<?php

declare(strict_types=1);

namespace Xutim\RedirectComponent\Domain\Factory;

use Xutim\RedirectComponent\Domain\Model\RedirectInterface;

interface RedirectFactoryInterface
{
    public function create(
        string $source,
        string $target,
        bool $permanent = false
    ): RedirectInterface;
}
