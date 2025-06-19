<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Action\Admin;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Twig\Environment;
use Xutim\CoreBundle\Service\ListFilterBuilder;
use Xutim\RedirectBundle\Domain\Model\RedirectInterface;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;

class ListRedirectsAction
{
    public function __construct(
        private readonly RedirectRepositoryInterface $repo,
        private readonly ListFilterBuilder $filterBuilder,
        private readonly Environment $twig
    ) {
    }

    public function __invoke(
        #[MapQueryParameter]
        string $searchTerm = '',
        #[MapQueryParameter]
        int $page = 1,
        #[MapQueryParameter]
        int $pageLength = 10,
        #[MapQueryParameter]
        string $orderColumn = 'id',
        #[MapQueryParameter]
        string $orderDirection = 'asc'
    ): Response {
        $filter = $this->filterBuilder->buildFilter($searchTerm, $page, $pageLength, $orderColumn, $orderDirection);

        /** @var QueryAdapter<RedirectInterface> $adapter */
        $adapter = new QueryAdapter($this->repo->queryByFilter($filter));
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        return new Response(
            $this->twig->render(
                '@XutimRedirect/admin/redirect/redirect_list.html.twig',
                [
                    'redirects' => $pager,
                    'filter' => $filter
                ]
            )
        );
    }
}
