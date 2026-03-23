<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Twig;

use Symfony\Component\Translation\LocaleSwitcher;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\SnippetBundle\Context\SnippetsContext;

class LocaleAwareSnippetExtension extends AbstractExtension
{
    public function __construct(
        private readonly SnippetsContext $context,
        private readonly LocaleSwitcher $localeSwitcher,
        private readonly string $defaultLocale,
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
        $dto = $this->context->getSnippet($code);
        if ($dto === null) {
            return '';
        }

        $locale = $this->localeSwitcher->getLocale();
        if ($dto->hasTranslation($locale)) {
            return $dto->getTranslation($locale);
        }

        if ($dto->hasTranslation($this->defaultLocale)) {
            return $dto->getTranslation($this->defaultLocale);
        }

        return '';
    }
}
