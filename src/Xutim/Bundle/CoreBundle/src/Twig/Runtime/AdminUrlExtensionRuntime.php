<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Runtime;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;

class AdminUrlExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly AdminUrlGenerator $router)
    {
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function generatePath(
        string $name,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        return $this->router->generate($name, $parameters, $referenceType);
    }
}
