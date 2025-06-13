<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Action\Admin;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Service\ListFilterBuilder;
use Xutim\EventBundle\Domain\Model\EventInterface;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventRepository;

#[Route('/event', name: 'admin_event_list', methods: ['get'])]
class ListEventsAction extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepo,
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
        string $orderColumn = EventRepository::FILTER_ORDER_COLUMN_MAP['id'],
        #[MapQueryParameter]
        string $orderDirection = 'asc'
    ): Response {
        $filter = $this->filterBuilder->buildFilter($searchTerm, $page, $pageLength, $orderColumn, $orderDirection);

        /** @var QueryAdapter<EventInterface> $adapter */
        $adapter = new QueryAdapter($this->eventRepo->queryByFilter($filter));
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        return $this->render('@XutimEvent/admin/event/event_list.html.twig', [
            'events' => $pager,
            'filter' => $filter
        ]);
    }
}
