<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

interface LocaleAwareInterface
{
    public function getLocale(): string;
}
