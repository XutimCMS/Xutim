<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Context;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Xutim\CoreBundle\Service\ListFilterBuilder;
use Xutim\EventBundle\Entity\Event;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventRepository;

class EventsContext
{
    public function __construct(
        private readonly EventRepository $eventRepo,
        private readonly ListFilterBuilder $filterBuilder
    ) {
    }

    /**
     * @return iterable<int|string, Event>
     */
    public function getUpcomingEvents(string $locale, int $page = 1, int $limit = 4): iterable
    {
        $filter = $this->filterBuilder->buildFilter('', $page, $limit, 'startsAt', 'asc');

        /** @var QueryAdapter<Event> $adapter */
        $adapter = new QueryAdapter($this->eventRepo->queryUpcomingPublishedByFilter($filter, $locale));
        /** @var Pagerfanta<Event> $pager*/
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        return $pager->getCurrentPageResults();
    }
}
