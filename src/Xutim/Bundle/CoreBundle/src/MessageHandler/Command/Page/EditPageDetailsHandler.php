<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\Page;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Domain\Event\Page\PageUpdatedEvent;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Message\Command\Page\EditPageDetailsCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Repository\PageRepository;

readonly class EditPageDetailsHandler implements CommandHandlerInterface
{
    public function __construct(
        private PageRepository $pageRepository,
        private EventRepository $eventRepository
    ) {
    }

    public function __invoke(EditPageDetailsCommand $cmd): void
    {
        $page = $this->pageRepository->find($cmd->pageId);
        if ($page === null) {
            throw new NotFoundHttpException('Page could not be found.');
        }

        $parentPage = $cmd->parentId !== null ? $this->pageRepository->find($cmd->parentId) : null;
        if ($this->pageRepository->wouldCreateLoop($page, $parentPage) === true) {
            throw new \Exception('Setting the new parent would create a loop.');
        }
        $page->change($cmd->color, $cmd->locales, $parentPage);

        $this->pageRepository->save($page, true);

        $pageUpdatedEvent = new PageUpdatedEvent(
            $page->getId(),
            $cmd->color,
            $cmd->locales,
            $page->getUpdatedAt(),
            $cmd->parentId
        );

        $logEntrySec = new Event(
            $page->getId(),
            $cmd->userIdentifier,
            Page::class,
            $pageUpdatedEvent
        );

        $this->eventRepository->save($logEntrySec, true);
    }
}
