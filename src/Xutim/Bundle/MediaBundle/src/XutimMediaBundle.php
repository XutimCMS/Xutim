<?php

declare(strict_types=1);

namespace Xutim\MediaBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
class XutimMediaBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
