<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Routing;

use LogicException;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\SnippetBundle\Repository\SnippetRepository;

class LocalizedRouteLoader extends Loader
{
    private bool $isLoaded = false;

    /**
     * Absolute path to the snippet route version file.
     *
     * This file is used as a cache dependency marker for all dynamically loaded
     * routes that are based on translatable snippets (e.g., "route.news" → "/en/news").
     *
     * Whenever a route-related snippet is changed (e.g., via admin panel),
     * this file should be "touched" (e.g., `file_put_contents($path, microtime())`)
     * to trigger Symfony's route cache invalidation.
     *
     * Both this loader and others that rely on route snippets (e.g. fallback slug loaders)
     * should depend on the same version file.
     *
     * Example path: "%kernel.cache_dir%/snippet_routes.version"
     */
    private readonly string $snippetVersionPath;

    public function __construct(
        private readonly SnippetRepository $snippetRepo,
        private readonly SiteContext $siteContext,
        string $snippetVersionPath,
        ?string $env = null,
    ) {
        parent::__construct($env);
        $this->snippetVersionPath = $snippetVersionPath;
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        if ($this->isLoaded) {
            throw new \RuntimeException('Loader already loaded.');
        }

        $availableLocales = implode('|', $this->siteContext->getLocales());
        $routes = new RouteCollection();
        $routes->addResource(new FileResource($this->snippetVersionPath));

        foreach (RouteSnippetRegistry::all() as $route) {
            $snippet = $this->snippetRepo->findByCode($route->snippetKey);
            if ($snippet === null) {
                throw new LogicException('RouteSnippetRegistry contains an invalid snippet code.');
            }

            foreach ($snippet->getTranslations() as $trans) {
                if (trim($trans->getContent()) === '') {
                    continue;
                }
                $locale = $trans->getLocale();
                $path = sprintf('/%s/%s', $trans->getLocale(), ltrim($trans->getContent(), '/'));

                $localizedRouteName = sprintf('xutim_%s.%s', $route->routeName, $locale);

                $routes->add($localizedRouteName, new Route(
                    path: $path,
                    defaults: array_merge([
                        '_controller' => $route->controller,
                        '_locale' => $trans->getLocale()
                    ], $route->defaults),
                    requirements: array_merge(
                        $route->requirements,
                        ['_locale' => $trans->getLocale()]
                    ),
                    host: $route->host,
                    options: [
                        'priority' => 90,
                    ]
                ));
            }
        }

        $this->isLoaded = true;
        return $routes;
    }

    public function supports($resource, ?string $type = null): bool
    {
        return $type === 'snippet_routes';
    }
}
