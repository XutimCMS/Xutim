<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

interface PublishableTranslationInterface
{
    public function isPublished(): bool;
}
