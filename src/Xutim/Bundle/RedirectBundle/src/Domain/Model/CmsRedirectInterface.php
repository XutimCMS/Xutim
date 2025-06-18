<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Domain\Model;

use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\RedirectComponent\Domain\Model\RedirectInterface;

interface CmsRedirectInterface extends RedirectInterface
{
    public function getTargetContentTranslation(): ContentTranslationInterface;

    public function change(
        string $source,
        ContentTranslationInterface $targetContentTranslation,
        ?string $locale = null,
        bool $permanent = false,
    ): void;
}
