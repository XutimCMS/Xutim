<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Sitemap;

use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\SnippetBundle\Repository\SnippetRepository;
use Xutim\SnippetBundle\Routing\RouteSnippetRegistry;

readonly class SitemapGenerator
{
    public function __construct(
        private RouterInterface $router,
        private PageRepository $pageRepo,
        private ArticleRepository $articleRepo,
        private TagRepository $tagRepo,
        private SnippetRepository $snippetRepo,
        private SiteContext $siteContext,
        private Environment $twig,
        private string $sitemapFile
    ) {
    }

    public function generate(): void
    {
        $items = array_merge(
            $this->getStaticRoutes(),
            $this->getPages(),
            $this->getArticles(),
            $this->getTags()
        );

        $xml = $this->twig->render('@XutimCore/sitemap/sitemap.xml.twig', [
            'items' => $items,
        ]);

        file_put_contents($this->sitemapFile, $xml);
    }

    /**
    * @return list<array{
    *     loc: string,
    *     lastmod: ?string,
    *     alternates: list<array{locale: string, url: string}>
    * }>
    */
    private function getStaticRoutes(): array
    {
        $locales = $this->siteContext->getMainLocales();
        $items = [];

        foreach ($locales as $locale) {
            $loc = $this->router->generate('homepage', ['_locale' => $locale], RouterInterface::ABSOLUTE_URL);

            $alternates = [];
            foreach ($locales as $altLocale) {
                if ($altLocale === $locale) {
                    continue;
                }
                $alternates[] = [
                    'locale' => $altLocale,
                    'url' => $this->router->generate('homepage', ['_locale' => $altLocale], RouterInterface::ABSOLUTE_URL),
                ];
            }

            $items[] = [
                'loc' => $loc,
                'lastmod' => null,
                'alternates' => $alternates,
            ];
        }

        foreach (RouteSnippetRegistry::all() as $route) {
            $snippetsByLocale = [];

            foreach ($locales as $locale) {
                $snippet = $this->snippetRepo->findByCode($route->snippetKey)?->getTranslationByLocale($locale);
                if ($snippet === null) {
                    continue;
                }

                $routeName = sprintf('xutim_%s.%s', $route->routeName, $locale);
                $snippetsByLocale[$locale] = $this->router->generate($routeName, [], RouterInterface::ABSOLUTE_URL);
            }

            foreach ($snippetsByLocale as $locale => $loc) {
                $alternates = [];

                foreach ($snippetsByLocale as $altLocale => $altUrl) {
                    if ($altLocale === $locale) {
                        continue;
                    }

                    $alternates[] = [
                        'locale' => $altLocale,
                        'url' => $altUrl,
                    ];
                }

                $items[] = [
                    'loc' => $loc,
                    'lastmod' => null,
                    'alternates' => $alternates,
                ];
            }
        }

        return $items;
    }

    private function getPages(): array
    {
        $items = [];

        /** @var Page $page */
        foreach ($this->pageRepo->findAll() as $page) {
            $translations = $page->getPublishedTranslations();

            foreach ($translations as $trans) {
                $items[] = [
                    'loc' => $this->router->generate('content_translation_show', [
                        'slug' => $trans->getSlug(),
                        '_locale' => $trans->getLocale(),
                    ], RouterInterface::ABSOLUTE_URL),
                    'lastmod' => $trans->getUpdatedAt(),
                    'alternates' => $this->buildAlternates($translations, 'page_show'),
                ];
            }
        }

        return $items;
    }

    private function getArticles(): array
    {
        $items = [];

        foreach ($this->articleRepo->findAllOnline() as $article) {
            $translations = array_filter($article->getTranslations(), fn ($t) => $t->isOnline());

            foreach ($translations as $trans) {
                $items[] = [
                    'loc' => $this->router->generate('article_show', [
                        'slug' => $trans->getSlug(),
                        '_locale' => $trans->getLocale(),
                    ], RouterInterface::ABSOLUTE_URL),
                    'lastmod' => $trans->getUpdatedAt(),
                    'alternates' => $this->buildAlternates($translations, 'article_show'),
                ];
            }
        }

        return $items;
    }

    private function getTags(): array
    {
        $items = [];

        foreach ($this->tagRepo->findAllVisible() as $tag) {
            $translations = array_filter($tag->getTranslations(), fn ($t) => $t->isOnline());

            foreach ($translations as $trans) {
                $items[] = [
                    'loc' => $this->router->generate('tag_show', [
                        'slug' => $trans->getSlug(),
                        '_locale' => $trans->getLocale(),
                    ], RouterInterface::ABSOLUTE_URL),
                    'lastmod' => null,
                    'alternates' => $this->buildAlternates($translations, 'tag_show'),
                ];
            }
        }

        return $items;
    }

    private function buildAlternates(array $translations, string $routeName): array
    {
        return array_map(fn ($trans) => [
            'locale' => $trans->getLocale(),
            'url' => $this->router->generate($routeName, [
                'slug' => $trans->getSlug(),
                '_locale' => $trans->getLocale(),
            ], RouterInterface::ABSOLUTE_URL),
        ], $translations);
    }
}
