<?php

declare(strict_types=1);

namespace Xutim\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
class XutimCoreBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
