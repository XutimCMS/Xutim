<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Context;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Repository\PageRepository;

final class PageTreeContext
{
    private const TAG_ALL = 'page_tree';

    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly TagAwareCacheInterface $cache
    ) {
    }

    /**
     * @return array{
     *      roots: array<string>,
     *      pages: array<string, array{page: PageInterface, translation: ContentTranslationInterface, children: list<string>}>
     * }
     */
    public function getTree(string $locale, bool $showArchived, bool $showOnlyTranslated): array
    {
        $key = $this->key($locale, $showArchived, $showOnlyTranslated);
        $tags = $this->tags($locale);

        /** @var array{
         *      roots: array<string>,
         *      pages: array<string, array{page: PageInterface, translation: ContentTranslationInterface, children: list<string>}>
         * } $result
         */
        $result = $this->cache->get(
            $key,
            /**
             * @return array{
             *      roots: array<string>,
             *      pages: array<string, array{page: PageInterface, translation: ContentTranslationInterface, children: list<string>}>
             * }
             */
            function (ItemInterface $item) use ($locale, $showArchived, $showOnlyTranslated, $tags): array {
                $item->expiresAfter(604800);
                $item->tag($tags);

                return $this->pageRepository->hierarchyByPublished($locale, $showArchived, $showOnlyTranslated);
            }
        );

        return $result;
    }

    public function resetForLocale(string $locale): void
    {
        $this->cache->delete($this->key($locale, false, true));
        $this->cache->delete($this->key($locale, false, false));
        $this->cache->delete($this->key($locale, true, true));
        $this->cache->delete($this->key($locale, true, false));
        $this->cache->invalidateTags($this->tags($locale));
    }

    public function resetAll(): void
    {
        $this->cache->invalidateTags([self::TAG_ALL]);
    }

    public function warmupForLocale(string $locale): void
    {
        $this->getTree($locale, false, true);
        $this->getTree($locale, false, false);
        $this->getTree($locale, true, true);
        $this->getTree($locale, true, false);
    }

    /** @param list<string> $locales */
    public function warmupAllLocales(array $locales): void
    {
        foreach ($locales as $loc) {
            $this->warmupForLocale($loc);
        }
    }

    private function key(string $locale, bool $showArchived, bool $showOnlyTranslated): string
    {
        return sprintf('page_tree.%s.%d.%d', $locale, (int) $showArchived, (int) $showOnlyTranslated);
    }

    /** @return list<string> */
    private function tags(string $locale): array
    {
        return [self::TAG_ALL, 'page_tree.' . $locale];
    }
}
