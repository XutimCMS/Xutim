<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension\Admin;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;

class ContentContextExtension extends AbstractExtension
{
    public function __construct(
        private readonly ContentContext $context,
        private readonly SiteContext $siteContext,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('content_context_language', [$this, 'getLanguage']),
            new TwigFunction('content_context_reference_language', [$this, 'getReferenceLanguage']),
        ];
    }

    public function getLanguage(): string
    {
        return $this->context->getLanguage();
    }

    public function getReferenceLanguage(): string
    {
        return $this->siteContext->getReferenceLocale();
    }
}
