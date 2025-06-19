<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Xutim\CoreBundle\Action\Public\ShowContentTranslation;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Repository\SnippetRepository;

class ContentTranslationRouteLoader extends Loader
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

        $mainLocales = implode('|', $this->siteContext->getLocales());
        $contentLocales = implode('|', $this->siteContext->getExtendedContentLocales());
        $routes = new RouteCollection();
        $usedSlugs = [];
        foreach (RouteSnippetRegistry::all() as $route) {
            $snippet = $this->snippetRepo->findByCode($route->snippetKey);


            foreach ($snippet->getTranslations() as $trans) {
                if (trim($trans->getContent()) === '') {
                    continue;
                }

                $slug = trim($trans->getContent(), '/');
                $usedSlugs[] = preg_quote($slug, '#');
            }
        }

        $blacklistPattern = implode('|', array_unique($usedSlugs));
        $slugRequirement = $blacklistPattern !== ''
            ? '^(?!' . $blacklistPattern . '$)[a-zA-Z0-9\-]+'
            : '[a-zA-Z0-9\-]+';

        $route = new Route(
            path: '/{_locale}/{slug}/{_content_locale}',
            defaults: [
                '_controller' => ShowContentTranslation::class,
                '_content_locale' => null,
            ],
            requirements: [
                'slug' => $slugRequirement,
                '_locale' => $mainLocales,
                '_content_locale' => $contentLocales,
            ],
            options: [
                'priority' => 0,
                'resource' => $this->snippetVersionPath,
            ]
        );

        $routes->add('content_translation_show', $route);
        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, ?string $type = null): bool
    {
        return $type === 'content_translation_fallback';
    }
}
