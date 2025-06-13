<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Infra\Layout;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Twig\ThemeFinder;

class LayoutLoader
{
    private const ARTICLE_REF = 'layouts-article';
    private const PAGE_REF = 'layouts-page';
    private const BLOCK_REF = 'layouts-block';
    private const TAG_REF = 'layouts-tag';

    public function __construct(
        private readonly LayoutFinder $finder,
        private readonly ThemeFinder $themeFinder,
        private readonly CacheInterface $layoutCache
    ) {
    }

    public function loadAllLayouts(): void
    {
        $this->layoutCache->delete(self::ARTICLE_REF);
        $this->layoutCache->delete(self::PAGE_REF);
        $this->layoutCache->delete(self::BLOCK_REF);
        $this->getArticleLayouts();
        $this->getPageLayouts();
        $this->getTagLayouts();
        $this->getBlockLayouts();
    }

    public function getBlockLayoutTemplate(string $code): string
    {
        $layout = $this->getBlockLayoutByCode($code);
        if ($layout === null) {
            $path = '/block/show.html.twig';
        } else {
            $path = sprintf('/layout/block/%s/layout.html.twig', $layout->path);
        }

        return $this->themeFinder->getActiveThemePath($path);
    }

    public function getArticleLayoutTemplate(?string $code): ?string
    {
        $layout = $this->getArticleLayoutByCode($code);
        if ($layout === null) {
            return null;
        }
        $path = sprintf('/layout/article/%s/layout.html.twig', $layout->path);

        return $this->themeFinder->getActiveThemePath($path);
    }

    public function getPageLayoutTemplate(?string $code): ?string
    {
        $layout = $this->getPageLayoutByCode($code);
        if ($layout === null) {
            return null;
        }
        $path = sprintf('/layout/page/%s/layout.html.twig', $layout->path);

        return $this->themeFinder->getActiveThemePath($path);
    }

    public function getTagLayoutTemplate(?string $code): ?string
    {
        $layout = $this->getTagLayoutByCode($code);
        if ($layout === null) {
            return null;
        }
        $path = sprintf('/layout/tag/%s/layout.html.twig', $layout->path);

        return $this->themeFinder->getActiveThemePath($path);
    }

    /**
     * @return list<Layout>
     */
    public function getArticleLayouts(): array
    {
        return $this->layoutCache->get(self::ARTICLE_REF, function (ItemInterface $item) {
            $item->expiresAfter(0);

            return $this->finder->findArticleLayouts();
        });
    }

    /**
     * @return list<Layout>
     */
    public function getPageLayouts(): array
    {
        return $this->layoutCache->get(self::PAGE_REF, function (ItemInterface $item) {
            $item->expiresAfter(0);

            return $this->finder->findPageLayouts();
        });
    }

    /**
     * @return list<Layout>
     */
    public function getBlockLayouts(): array
    {
        return $this->layoutCache->get(self::BLOCK_REF, function (ItemInterface $item) {
            $item->expiresAfter(0);

            return $this->finder->findBlockLayouts();
        });
    }

    /**
     * @return list<Layout>
     */
    public function getTagLayouts(): array
    {
        return $this->layoutCache->get(self::TAG_REF, function (ItemInterface $item) {
            $item->expiresAfter(0);

            return $this->finder->findTagLayouts();
        });
    }

    public function getArticleLayoutByCode(?string $code): ?Layout
    {
        return $this->getLayoutByCode($code, $this->getArticleLayouts());
    }

    public function getPageLayoutByCode(?string $code): ?Layout
    {
        return $this->getLayoutByCode($code, $this->getPageLayouts());
    }

    public function getBlockLayoutByCode(string $code): ?Layout
    {
        return $this->getLayoutByCode($code, $this->getBlockLayouts());
    }

    public function getTagLayoutByCode(?string $code): ?Layout
    {
        return $this->getLayoutByCode($code, $this->getTagLayouts());
    }

    /**
     * @param list<Layout> $layouts
     */
    private function getLayoutByCode(?string $code, array $layouts): ?Layout
    {
        if ($code === null) {
            return null;
        }

        foreach ($layouts as $layout) {
            if ($layout->code === $code) {
                return $layout;
            }
        }

        return null;
    }
}
