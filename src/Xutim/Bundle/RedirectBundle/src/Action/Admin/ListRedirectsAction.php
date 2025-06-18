<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Action\Admin;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Service\ListFilterBuilder;
use Xutim\RedirectBundle\Domain\Model\CmsRedirectInterface;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;

#[Route('/admin/redirect', name: 'admin_redirect_list', methods: ['get'])]
class ListRedirectsAction extends AbstractController
{
    public function __construct(
        private readonly RedirectRepositoryInterface $repo,
        private readonly ListFilterBuilder $filterBuilder
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

        /** @var QueryAdapter<CmsRedirectInterface> $adapter */
        $adapter = new QueryAdapter($this->repo->queryByFilter($filter));
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        return $this->render('@XutimRedirect/admin/redirect/redirect_list.html.twig', [
            'redirects' => $pager,
            'filter' => $filter
        ]);
    }
}
