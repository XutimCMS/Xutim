<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Runtime;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\TagTranslationRepository;
use Xutim\SnippetBundle\Routing\RouteSnippetRegistry;
use Xutim\SnippetBundle\Routing\SnippetUrlGenerator;

class RouteGuesserExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly RequestStack $reqStack,
        private readonly ContentTranslationRepository $repo,
        private readonly TagTranslationRepository $tagRepo,
        private readonly RouterInterface $router,
        private readonly SnippetUrlGenerator $snippetUrlGenerator,
    ) {
    }

    /**
     * @param array<string, mixed> $params
     */
    public function snippetPath(string $routeName, string $locale, array $params = []): string
    {
        return $this->snippetUrlGenerator->generate($routeName, $locale, $params);
    }

    public function isSnippetRoute(string $routeName): bool
    {
        $request = $this->reqStack->getMainRequest();
        if ($request === null) {
            return false;
        }

        return $this->snippetUrlGenerator->matches(
            $request->attributes->getString('_route'),
            $routeName,
        );
    }

    /**
     * Generates a URL for the current route with extra params merged in.
     * Dispatches between snippet routes (handled by SnippetUrlGenerator) and
     * regular Symfony routes (handled by the router).
     *
     * @param array<string, mixed> $params
     */
    public function currentPathWith(array $params = []): string
    {
        $request = $this->reqStack->getMainRequest();
        if ($request === null) {
            return '';
        }

        $route = $request->attributes->getString('_route');

        if (preg_match('#^xutim_(?<name>[^.]+)\.(?<locale>.+)$#', $route, $matches) === 1) {
            // Snippet routes don't have a _content_locale URL segment — drop it
            // so it doesn't end up as a stray query param.
            unset($params['_content_locale']);

            return $this->snippetUrlGenerator->generate($matches['name'], $matches['locale'], $params);
        }

        /** @var array<string, mixed> $existingRouteParams */
        $existingRouteParams = $request->attributes->get('_route_params', []);

        return $this->router->generate($route, array_merge($existingRouteParams, $params));
    }

    public function switchLocaleRoute(string $locale): string
    {
        $request = $this->reqStack->getMainRequest();
        if ($request === null) {
            return '';
        }

        $currentRouteName = $request->attributes->getString('_route');
        $slug = $request->attributes->getString('slug');
        $currentLocale = $request->getLocale();
        switch ($currentRouteName) {
            case 'content_translation_show':
                if ($slug !== '') {
                    $content = $this->repo->findOneBy(['slug' => $slug]);
                    if ($content !== null) {
                        $translation = $content->getObject()->getTranslationByLocale($locale);
                        if ($translation !== null) {
                            return $this->router->generate('content_translation_show', ['_locale' => $locale, 'slug' => $translation->getSlug()]);
                        }
                    }
                }
                break;
            case 'tag_translation_show':
                if ($slug !== '') {
                    $content = $this->tagRepo->findOneBy(['slug' => $slug]);
                    if ($content !== null) {
                        $translation = $content->getTag()->getTranslationByLocale($locale);
                        if ($translation !== null && $content->getTag()->isPublished()) {
                            return $this->router->generate('tag_translation_show', ['_locale' => $locale, 'slug' => $translation->getSlug()]);
                        }
                    }
                }
                break;
        }
        foreach (RouteSnippetRegistry::all() as $route) {
            if ($currentRouteName === sprintf('xutim_%s.%s', $route->routeName, $currentLocale)) {
                return $this->snippetUrlGenerator->generate($route->routeName, $locale);
            }
        }

        return $this->router->generate('homepage', ['_locale' => $locale]);
    }
}
