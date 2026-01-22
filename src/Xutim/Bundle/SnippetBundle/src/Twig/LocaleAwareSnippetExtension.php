<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\SnippetBundle\Context\SnippetsContext;

class LocaleAwareSnippetExtension extends AbstractExtension
{
    public function __construct(
        private readonly SnippetsContext $context,
        private readonly RequestStack $requestStack
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_snippet', [$this, 'renderSnippet'], ['is_safe' => ['html']]),
        ];
    }

    public function renderSnippet(string $code): string
    {
        $contentLocale = $this->requestStack->getMainRequest()?->attributes->get('_content_locale', null);
        $locale = is_string($contentLocale) ? $contentLocale : ($this->requestStack->getMainRequest()?->getLocale() ?? 'en');
        $dto = $this->context->getSnippet($code);

        if ($dto !== null && $dto->hasTranslation($locale)) {
            return $dto->getTranslation($locale);
        }

        return '';
    }
}
