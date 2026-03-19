<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class XutimNotificationBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
