<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Sitemap;

use DateTimeImmutable;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\TagTranslationInterface;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Routing\ContentTranslationRouteGenerator;
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
        private ContentTranslationRouteGenerator $transRouteGenerator,
        private Environment $twig,
        private string $sitemapFile,
        private string $appHost
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
    *     changefreq: string,
    *     alternates: list<array{locale: string, url: string}>
    * }>
    */
    private function getStaticRoutes(): array
    {
        $locales = $this->siteContext->getMainLocales();
        $items = [];

        foreach ($locales as $locale) {
            $loc = $this->getAbsoluteUrl($this->router->generate('homepage', ['_locale' => $locale]));

            $alternates = [];
            foreach ($locales as $altLocale) {
                if ($altLocale === $locale) {
                    continue;
                }
                $alternates[] = [
                    'locale' => $altLocale,
                    'url' => $this->getAbsoluteUrl($this->router->generate('homepage', ['_locale' => $altLocale])),
                ];
            }

            $items[] = [
                'loc' => $loc,
                'lastmod' => null,
                'changefreq' => 'daily',
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
                $snippetsByLocale[$locale] = $this->getAbsoluteUrl($this->router->generate($routeName));
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
                    'changefreq' => 'daily',
                    'alternates' => $alternates,
                ];
            }
        }

        return $items;
    }

    /**
     * @return list<array{
     *     loc: string,
     *     lastmod: DateTimeImmutable,
     *     alternates: array<array{locale: string, url: string}>
     * }>
     */
    private function getPages(): array
    {
        $items = [];

        /** @var Page $page */
        foreach ($this->pageRepo->findAll() as $page) {
            $translations = $page->getPublishedTranslations();

            foreach ($translations as $trans) {
                $items[] = [
                    'loc' => $this->getAbsoluteUrl(
                        $this->transRouteGenerator->generatePath($trans, null)
                    ),
                    'lastmod' => $trans->getUpdatedAt(),
                    'alternates' => $this->buildContentTransAlternates($translations->toArray()),
                ];
            }
        }

        return $items;
    }

    /**
     * @return list<array{
     *     loc: string,
     *     lastmod: DateTimeImmutable,
     *     alternates: array<array{locale: string, url: string}>
     * }>
     */
    private function getArticles(): array
    {
        $items = [];

        foreach ($this->articleRepo->findAll() as $article) {
            $translations = $article->getPublishedTranslations();

            foreach ($translations as $trans) {
                $items[] = [
                    'loc' => $this->getAbsoluteUrl(
                        $this->transRouteGenerator->generatePath($trans, null)
                    ),
                    'lastmod' => $trans->getUpdatedAt(),
                    'alternates' => $this->buildContentTransAlternates($translations->toArray()),
                ];
            }
        }

        return $items;
    }

    /**
     * @return list<array{
     *     loc: string,
     *     lastmod: null,
     *     alternates: array<array{locale: string, url: string}>
     * }>
     */
    private function getTags(): array
    {
        $items = [];

        foreach ($this->tagRepo->findAllPublished() as $tag) {
            $translations = $tag->getTranslations()->filter(
                fn ($trans) => in_array(
                    $trans->getLocale(),
                    $this->siteContext->getMainLocales(),
                    true
                )
            );

            foreach ($translations as $trans) {
                $items[] = [
                    'loc' => $this->getAbsoluteUrl(
                        $this->router->generate('tag_translation_show', [
                            'slug' => $trans->getSlug(),
                            '_locale' => $trans->getLocale(),
                        ])
                    ),
                    'lastmod' => null,
                    'alternates' => $this->buildTagAlternates($translations->toArray()),
                ];

                $filter = new FilterDto('', 1, 12);
                /** @var QueryAdapter<ArticleInterface> $adapter */
                $adapter = new QueryAdapter($this->articleRepo->queryPublishedByTagAndFilter($filter, $tag, $trans->getLocale()));
                $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
                    $adapter,
                    $filter->page,
                    $filter->pageLength
                );
                $totalPages = $pager->getNbPages();
                for ($page = 2; $page <= $totalPages; $page++) {
                    $url = $this->getAbsoluteUrl(
                        $this->router->generate('tag_translation_show', [
                            '_locale' => $trans->getLocale(),
                            'slug' => $trans->getSlug(),
                            'page' => $page,
                        ])
                    );

                    $items[] = [
                        'loc' => $url,
                        'lastmod' => null,
                        'alternates' => [],
                    ];
                }
            }
        }


        return $items;
    }

    /**
     * @param array<TagTranslationInterface> $translations
     *
     * @return array<array{locale: string, url: string}>
     */
    private function buildTagAlternates(array $translations): array
    {
        if (count($translations) <= 1) {
            return [];
        }
        return array_map(fn ($trans) => [
            'locale' => $trans->getLocale(),
            'url' => $this->getAbsoluteUrl(
                $this->router->generate('tag_translation_show', [
                    '_locale' => $trans->getLocale(),
                    'slug' => $trans->getSlug(),
                ])
            )
        ], $translations);
    }
    /**
     * @param array<ContentTranslationInterface> $translations
     *
     * @return array<array{locale: string, url: string}>
     */
    private function buildContentTransAlternates(array $translations): array
    {
        if (count($translations) <= 1) {
            return [];
        }
        return array_map(fn ($trans) => [
            'locale' => $trans->getLocale(),
            'url' => $this->getAbsoluteUrl(
                $this->transRouteGenerator->generatePath(
                    $trans,
                    null
                )
            ),
        ], $translations);
    }

    private function getAbsoluteUrl(string $url): string
    {
        return sprintf('https://%s%s', $this->appHost, $url);
    }
}
