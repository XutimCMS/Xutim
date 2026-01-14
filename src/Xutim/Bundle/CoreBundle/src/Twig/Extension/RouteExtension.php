<?php

declare(strict_types=1);
namespace Xutim\CoreBundle\Twig\Extension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
            new TwigFunction('content_translation_path', [$this, 'generateContentTranslationPath']),
        ];
    }

    /**
     * @param array<string, string> $params
     */
    public function generateContentTranslationPath(
        ContentTranslationInterface $trans,
        ?string $mainLocale,
        array $params = [],
        int $referencePath = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        return $this->routeGenerator->generatePath($trans, $mainLocale, $referencePath, $params);
    }
}
