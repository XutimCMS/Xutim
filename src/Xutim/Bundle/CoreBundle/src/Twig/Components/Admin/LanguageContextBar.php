<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Components\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class LanguageContextBar
{
    public object|null $entity = null;

    /**
    * Can either be translated or untranslated.
    */
    public bool $simpleTranslation = false;

    public bool $showExtendedLanguages = true;
}
