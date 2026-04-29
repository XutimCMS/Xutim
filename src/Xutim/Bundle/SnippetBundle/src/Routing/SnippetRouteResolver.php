<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Routing;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Xutim\CoreBundle\Routing\Dynamic\ControllerMatch;
use Xutim\CoreBundle\Routing\Dynamic\DynamicMatch;
use Xutim\CoreBundle\Routing\Dynamic\DynamicRouteResolverInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

final readonly class SnippetRouteResolver implements DynamicRouteResolverInterface
{
    public function __construct(
        private SnippetRepositoryInterface $snippetRepo,
        private LoggerInterface $logger
    ) {
    }

    public function resolve(string $path, Request $request): ?DynamicMatch
    {
        foreach (RouteSnippetRegistry::all() as $descriptor) {
            $snippet = $this->snippetRepo->findByCode($descriptor->snippetKey);
            if ($snippet === null) {
                $this->logger->warning('Route snippet missing', [
                    'snippetKey' => $descriptor->snippetKey,
                    'routeName' => $descriptor->routeName,
                ]);

                continue;
            }

            foreach ($snippet->getTranslations() as $translation) {
                $snippetRoute = trim($translation->getContent(), '/');
                if ($snippetRoute === '') {
                    $this->logger->warning('Route snippet translation is missing', [
                        'snippetKey' => $descriptor->snippetKey,
                        'routeName' => $descriptor->routeName,
                        'locale' => $translation->getLocale(),
                    ]);

                    continue;
                }

                $expectedPath = sprintf('/%s/%s', $translation->getLocale(), $snippetRoute);
                if ($expectedPath !== $path) {
                    continue;
                }

                return new ControllerMatch(
                    controller: $descriptor->controller,
                    name: sprintf('xutim_%s.%s', $descriptor->routeName, $translation->getLocale()),
                    attributes: ['_locale' => $translation->getLocale()]
                );
            }
        }

        return null;
    }
}
