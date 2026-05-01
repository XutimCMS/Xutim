<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Routing;

use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

final readonly class SnippetUrlGenerator
{
    public function __construct(
        private SnippetRepositoryInterface $snippetRepo,
    ) {
    }

    /**
     * @param array<string, mixed> $params
     */
    public function generate(string $routeName, string $locale, array $params = []): string
    {
        foreach (RouteSnippetRegistry::all() as $descriptor) {
            if ($descriptor->routeName !== $routeName) {
                continue;
            }
            $snippet = $this->snippetRepo->findByCode($descriptor->snippetKey);
            $translation = $snippet?->getTranslationByLocale($locale);
            if ($translation === null) {
                return '';
            }
            $content = trim($translation->getContent(), '/');
            if ($content === '') {
                return '';
            }

            $url = sprintf('/%s/%s', $locale, $content);
            if ($params === []) {
                return $url;
            }

            return $url . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        }

        return '';
    }

    public function matches(string $currentRouteName, string $routeName): bool
    {
        return str_starts_with($currentRouteName, sprintf('xutim_%s.', $routeName));
    }
}
