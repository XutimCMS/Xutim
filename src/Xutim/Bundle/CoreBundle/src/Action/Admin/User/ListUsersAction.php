<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\User;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Repository\UserRepository;
use Xutim\CoreBundle\Service\ListFilterBuilder;

#[Route('/user', name: 'admin_user_list')]
class ListUsersAction extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
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
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        $filter = $this->filterBuilder->buildFilter($searchTerm, $page, $pageLength, $orderColumn, $orderDirection);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($this->userRepository->queryByFilter($filter)),
            $filter->page,
            $filter->pageLength
        );

        return $this->render('@XutimCore/admin/user/list.html.twig', [
            'users' => $pager,
            'filter' => $filter
        ]);
    }
}
