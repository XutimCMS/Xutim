<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\RedirectBundle\Domain\Model\CmsRedirectInterface;

interface CmsRedirectFactoryInterface
{
    public function create(
        string $source,
        ContentTranslationInterface $targetContentTranslation,
        ?string $locale = null,
        bool $permanent = false,
    ): CmsRedirectInterface;
}
