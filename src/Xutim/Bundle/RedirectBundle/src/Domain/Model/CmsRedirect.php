<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Domain\Model;

use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\RedirectComponent\Domain\Model\Redirect as ModelRedirect;

abstract class CmsRedirect extends ModelRedirect implements CmsRedirectInterface
{
    protected ContentTranslationInterface $targetContentTranslation;

    public function __construct(
        string $source,
        ContentTranslationInterface $targetContentTranslation,
        ?string $locale = null,
        bool $permanent = false,
    ) {
        parent::__construct($source, '', $locale, $permanent);
        $this->targetContentTranslation = $targetContentTranslation;
    }

    public function change(
        string $source,
        ContentTranslationInterface $targetContentTranslation,
        ?string $locale = null,
        bool $permanent = false,
    ): void {
        $this->source = $source;
        $this->targetContentTranslation = $targetContentTranslation;
        $this->locale = $locale;
        $this->permanent = $permanent;
    }

    public function getTargetContentTranslation(): ContentTranslationInterface
    {
        return $this->targetContentTranslation;
    }
}
