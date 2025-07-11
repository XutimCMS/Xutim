<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Action;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Twig\Environment;
use Xutim\CoreBundle\Service\ListFilterBuilder;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

class ListSnippetsAction
{
    public function __construct(
        private readonly SnippetRepositoryInterface $repo,
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
        int $pageLength = 50,
        #[MapQueryParameter]
        string $orderColumn = '',
        #[MapQueryParameter]
        string $orderDirection = 'asc'
    ): Response {
        $filter = $this->filterBuilder->buildFilter($searchTerm, $page, $pageLength, $orderColumn, $orderDirection);

        /** @var QueryAdapter<SnippetInterface> $adapter */
        $adapter = new QueryAdapter($this->repo->queryByFilter($filter));
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        $html = $this->twig->render('@XutimSnippet/admin/snippet/snippet_list.html.twig', [
            'snippets' => $pager,
            'filter' => $filter
        ]);

        return new Response($html);
    }
}
