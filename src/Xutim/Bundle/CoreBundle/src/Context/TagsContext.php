<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Context;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Service\ListFilterBuilder;

class TagsContext
{
    public function __construct(
        private readonly ArticleRepository $articleRepo,
        private readonly ListFilterBuilder $filterBuilder
    ) {
    }

    /**
     * @return iterable<int|string, Article>
     */
    public function getRecentArticlesByTag(TagInterface $tag, string $locale): iterable
    {
        $filter = $this->filterBuilder->buildFilter('', 1, 4, 'publishedAt', 'desc');

        /** @var QueryAdapter<Article> $adapter */
        $adapter = new QueryAdapter($this->articleRepo->queryPublishedByTagAndFilter($filter, $tag, $locale));
        /** @var Pagerfanta<Article> $pager*/
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        return $pager->getCurrentPageResults();
    }

    public function getRecentArticleByTag(TagInterface $tag, string $locale): ?Article
    {
        $filter = $this->filterBuilder->buildFilter('', 1, 1, 'publishedAt', 'desc');

        /** @var QueryAdapter<Article> $adapter */
        $adapter = new QueryAdapter($this->articleRepo->queryPublishedByTagAndFilter($filter, $tag, $locale));
        /** @var Pagerfanta<Article> $pager*/
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        $articles = $pager->getCurrentPageResults();
        foreach ($pager->getCurrentPageResults() as $article) {
            return $article;
        }

        return null;
    }
}
