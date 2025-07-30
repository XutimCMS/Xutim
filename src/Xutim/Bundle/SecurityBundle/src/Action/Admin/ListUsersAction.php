<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Action\Admin;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;
use Xutim\CoreBundle\Service\ListFilterBuilder;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;
use Xutim\SecurityBundle\Security\UserRoles;

class ListUsersAction
{
    public function __construct(
        private readonly ListFilterBuilder $filterBuilder,
        private readonly UserRepositoryInterface $userRepo,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly Environment $twig,
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
        if ($this->authChecker->isGranted(UserRoles::ROLE_ADMIN) === false) {
            throw new AccessDeniedException('Access denied.');
        }
        $filter = $this->filterBuilder->buildFilter($searchTerm, $page, $pageLength, $orderColumn, $orderDirection);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($this->userRepo->queryByFilter($filter)),
            $filter->page,
            $filter->pageLength
        );

        return new Response($this->twig->render('@XutimSecurity/user/list.html.twig', [
            'users' => $pager,
            'filter' => $filter
        ]));
    }
}
