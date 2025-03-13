<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension\Admin;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\CoreBundle\Context\Admin\ContentContext;

class ContentContextExtension extends AbstractExtension
{
    public function __construct(private readonly ContentContext $context)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('content_context_language', [$this, 'getLanguage']),
        ];
    }

    public function getLanguage(): string
    {
        return $this->context->getLanguage();
    }
}
