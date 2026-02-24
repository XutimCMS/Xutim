<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Context;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Service\ListFilterBuilder;

class NewsContext
{
    public function __construct(
        private readonly ArticleRepository $articleRepo,
        private readonly ListFilterBuilder $filterBuilder
    ) {
    }

    /**
     * @return Pagerfanta<Article>
     */
    public function getNews(string $locale, int $page = 1): Pagerfanta
    {
        $filter = $this->filterBuilder->buildFilter('', $page, 12, 'publishedAt', 'desc');

        /** @var QueryAdapter<Article> $adapter */
        $adapter = new QueryAdapter($this->articleRepo->queryPublishedNewsByFilter($filter, $locale));
        /** @var Pagerfanta<Article> $pager*/
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        return $pager;
    }

    /**
     * @return Pagerfanta<Article>
     */
    public function getRecentNews(string $locale): Pagerfanta
    {
        $filter = $this->filterBuilder->buildFilter('', 1, 4, 'publishedAt', 'desc');

        /** @var QueryAdapter<Article> $adapter */
        $adapter = new QueryAdapter($this->articleRepo->queryPublishedNewsByFilter($filter, $locale));
        /** @var Pagerfanta<Article> $pager*/
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        return $pager;
    }
}
