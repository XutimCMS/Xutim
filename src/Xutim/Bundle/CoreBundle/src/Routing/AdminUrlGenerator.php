<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Xutim\CoreBundle\Context\Admin\ContentContext;

final readonly class AdminUrlGenerator
{
    public function __construct(
        private UrlGeneratorInterface $router,
        private ContentContext $contentContext
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function generate(
        string $name,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        $parameters['_content_locale'] ??= $this->contentContext->getLanguage();

        return $this->router->generate($name, $parameters, $referenceType);
    }
}
