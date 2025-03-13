<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Snippet;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\Snippet;
use Xutim\CoreBundle\Repository\SnippetRepository;
use Xutim\CoreBundle\Service\ListFilterBuilder;

#[Route('/snippet', name: 'admin_snippet_list', methods: ['GET'])]
class ListSnippetsAction extends AbstractController
{
    public function __construct(
        private readonly SnippetRepository $repo,
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
        string $orderColumn = '',
        #[MapQueryParameter]
        string $orderDirection = 'asc'
    ): Response {
        $filter = $this->filterBuilder->buildFilter($searchTerm, $page, $pageLength, $orderColumn, $orderDirection);

        /** @var QueryAdapter<Snippet> $adapter */
        $adapter = new QueryAdapter($this->repo->queryByFilter($filter));
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        return $this->render('@XutimCore/admin/snippet/snippet_list.html.twig', [
            'snippets' => $pager,
            'filter' => $filter
        ]);
    }
}
