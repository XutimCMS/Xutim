<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Symfony\Component\Routing\RouterInterface;
use Xutim\CoreBundle\Entity\MenuItem;
use Xutim\CoreBundle\Repository\MenuItemRepository;

final readonly class MenuBuilder
{
    public function __construct(
        private MenuItemRepository $repo,
        private RouterInterface $router
    ) {
    }

    /**
     * @return array{
     *      roots: array<string>,
     *      items: array<string, array{item: MenuItem, children: list<string>}>
     * }
     */
    public function constructHierarchy(): array
    {
        $items = $this->repo->findByHierarchy();

        /** @var array<string> $rootItemsId */
        $rootItemsId = [];
        /** @var array<string, array{item: MenuItem, children: list<string>}> $itemsMap */
        $itemsMap = [];

        foreach ($items as $item) {
            $itemId = $item->getId()->toRfc4122();
            if ($item->getParent() === null) {
                $rootItemsId[] = $itemId;
            }

            $itemsMap[$itemId] = [
                'item' => $item,
                'children' => [],
            ];
        }

        foreach ($items as $item) {
            if ($item->getParent() !== null) {
                $parentId = $item->getParent()->getId()->toRfc4122();
                $itemsMap[$parentId]['children'][] = $item->getId()->toRfc4122();
            }
        }

        return [
            'roots' => $rootItemsId,
            'items' => $itemsMap,
        ];
    }

    /**
     * @return array{
     *      roots: array<string>,
     *      items: array<string, array{
     *          children: list<string>,
     *          translations: array<string, array{name: string, route: string, hasLink: bool}>
     *      }>
     * }
     */
    public function buildMenu(): array
    {
        $items = $this->repo->findByHierarchy();

        /** @var array<string> $rootItemsId */
        $rootItemsId = [];
        /** @var array<string, array{children: list<string>, translations: array<string, array{name: string, route: string, hasLink: bool}>}> $itemsMap */
        $itemsMap = [];

        foreach ($items as $item) {
            $itemId = $item->getId()->toRfc4122();
            if ($item->getParent() === null) {
                $rootItemsId[] = $itemId;
            }

            $translations = $this->generateTranslations($item);
            $itemsMap[$itemId] = [
                'children' => [],
                'translations' => $translations,
            ];
        }

        foreach ($items as $item) {
            if ($item->getParent() !== null) {
                $parentId = $item->getParent()->getId()->toRfc4122();
                $itemsMap[$parentId]['children'][] = $item->getId()->toRfc4122();
            }
        }

        return [
            'roots' => $rootItemsId,
            'items' => $itemsMap,
        ];
    }

    /**
     * @return array<string, array{name: string, route: string, hasLink: bool}>
     */
    private function generateTranslations(MenuItem $item): array
    {
        $translations = [];

        foreach ($item->getObject()->getTranslations() as $trans) {
            if ($trans->isPublished()) {
                $translations[$trans->getLocale()] = [
                    'name' => $trans->getTitle(),
                    'route' => $this->router->generate(
                        'content_translation_show',
                        [
                            '_locale' => $trans->getLocale(),
                            'slug' => $trans->getSlug(),
                        ]
                    ),
                    'hasLink' => $item->hasLink()
                ];
            }
        }

        return $translations;
    }
}
