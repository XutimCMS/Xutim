<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension\Public;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\CoreBundle\Entity\Page;

class BreadcrumbsRendererExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('page_breadcrumbs_items', [$this, 'getPageItems'])
        ];
    }

    /**
     * @return array<Page>
     */
    public function getPageItems(Page $page): array
    {
        $items = [];
        $currentPage = $page;
        while ($currentPage !== null) {
            $items[] = $currentPage;
            $currentPage = $currentPage->getParent();
        }

        return array_reverse($items);
    }
}
