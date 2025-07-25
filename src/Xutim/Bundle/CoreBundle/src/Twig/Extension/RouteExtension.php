<?php

declare(strict_types=1);
namespace Xutim\CoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Routing\ContentTranslationRouteGenerator;

class RouteExtension extends AbstractExtension
{
    public function __construct(
        private readonly ContentTranslationRouteGenerator $routeGenerator
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('content_translation_path', [$this, 'generatePath']),
        ];
    }

    public function generatePath(
        ContentTranslationInterface $trans,
        string $mainLocale
    ): string {
        return $this->routeGenerator->generatePath($trans, $mainLocale);
    }
}
