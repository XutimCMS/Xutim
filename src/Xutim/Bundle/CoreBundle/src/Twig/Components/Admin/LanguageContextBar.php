<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Components\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Entity\Page;

#[AsTwigComponent]
final class LanguageContextBar
{
    public Article|Page|File|null $entity = null;

    /**
    * Can either be translated or untranslated.
    */
    public bool $simpleTranslation = false;
}
