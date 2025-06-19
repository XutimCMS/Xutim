<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
class XutimRedirectBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
