<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Domain\Model;

use Xutim\RedirectComponent\Domain\Model\RedirectInterface as ModelRedirectInterface;

interface RedirectInterface extends ModelRedirectInterface
{
    public function change(
        string $source,
        string $target,
        bool $permanent = false,
    ): void;
}
