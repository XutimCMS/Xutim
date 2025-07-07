<?php

declare(strict_types=1);

namespace Xutim\RedirectComponent\Domain;

use Xutim\RedirectComponent\Domain\Model\RedirectInterface;

interface RedirectTargetResolverInterface
{
    public function resolveTargetUrl(RedirectInterface $redirect): ?string;
}
