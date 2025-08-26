<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Service\ListFilterBuilder;

class ListArticlesAction extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly ListFilterBuilder $filterBuilder,
        private readonly ContentContext $contentContext,
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapQueryParameter]
        string $searchTerm = '',
        #[MapQueryParameter]
        int $page = 1,
        #[MapQueryParameter]
        int $pageLength = 10,
        #[MapQueryParameter]
        string $orderColumn = '',
        #[MapQueryParameter]
        string $orderDirection = 'asc',
    ): Response {
        /** @var array<string,string> $cols */
        $cols = $request->query->all('col');
        $filter = $this->filterBuilder->buildFilter($searchTerm, $page, $pageLength, $orderColumn, $orderDirection, $cols);

        /** @var QueryAdapter<Article> $adapter */
        $adapter = new QueryAdapter($this->articleRepository->queryByFilter($filter, $this->contentContext->getLocale()));
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        return $this->render('@XutimCore/admin/article/article_list.html.twig', [
            'articles' => $pager,
            'filter' => $filter
        ]);
    }
}
