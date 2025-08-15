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
    public function getTree(?string $locale, bool $showArchived): array
    {
        $key = $this->key($locale, $showArchived);
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
            function (ItemInterface $item) use ($locale, $showArchived, $tags): array {
                $item->expiresAfter(604800);
                $item->tag($tags);

                return $this->pageRepository->hierarchyByPublished($locale, $showArchived);
            }
        );

        return $result;
    }

    public function resetForLocale(?string $locale): void
    {
        $this->cache->delete($this->key($locale, false));
        $this->cache->delete($this->key($locale, true));
        $this->cache->invalidateTags($this->tags($locale));
    }

    public function resetAll(): void
    {
        $this->cache->invalidateTags([self::TAG_ALL]);
    }

    public function warmupForLocale(?string $locale): void
    {
        $this->getTree($locale, false);
        $this->getTree($locale, true);
    }

    /** @param list<string> $locales */
    public function warmupAllLocales(array $locales): void
    {
        foreach ($locales as $loc) {
            $this->warmupForLocale($loc);
        }
    }

    private function key(?string $locale, bool $showArchived): string
    {
        $loc = $locale ?? '_any';
        return sprintf('page_tree.%s.%d', $loc, (int) $showArchived);
    }

    /** @return list<string> */
    private function tags(?string $locale): array
    {
        $loc = $locale ?? '_any';
        return [self::TAG_ALL, 'page_tree.' . $loc];
    }
}
