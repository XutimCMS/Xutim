<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Block;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Xutim\CoreBundle\Repository\BlockRepository;
use Xutim\CoreBundle\Service\ListFilterBuilder;
use Xutim\SecurityBundle\Security\UserRoles;

class ListBlocksAction extends AbstractController
{
    public function __construct(
        private readonly BlockRepository $blockRepo,
        private readonly ListFilterBuilder $filterBuilder
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
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $filter = $this->filterBuilder->buildFilter($searchTerm, $page, $pageLength, $orderColumn, $orderDirection);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($this->blockRepo->queryByFilter($filter)),
            $filter->page,
            $filter->pageLength
        );

        return $this->render('@XutimCore/admin/block/block_list.html.twig', [
            'blocks' => $pager,
            'filter' => $filter
        ]);
    }
}
