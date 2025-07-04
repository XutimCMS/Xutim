<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension\Admin;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;

class TranslatorAuthCheckerExtension extends AbstractExtension
{
    public function __construct(
        private readonly TranslatorAuthChecker $authChecker
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_granted_translation', [$this, 'isGranted']),
        ];
    }

    public function isGranted(string $locale): bool
    {
        return $this->authChecker->canTranslate($locale);
    }
}
