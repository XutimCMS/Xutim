<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\PublicationStatus;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Event\PublicationStatus\PublicationStatusChangedEvent;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Message\Command\PublicationStatus\ChangePublicationStatusCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\EventRepository;

readonly class ChangeStatusHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContentTranslationRepository $contentTransRepo,
        private EventRepository $eventRepo,
        private SiteContext $siteContext
    ) {
    }

    public function __invoke(ChangePublicationStatusCommand $cmd): void
    {
        $trans = $this->contentTransRepo->find($cmd->objectId);

        if ($trans === null) {
            throw new NotFoundHttpException(sprintf(
                'The given content translation with id: "%s" could not be found',
                $cmd->objectId
            ));
        }

        if ($trans->isInStatus($cmd->status)) {
            return;
        }

        $trans->changeStatus($cmd->status);
        $this->entityManager->flush();
        $this->siteContext->resetMenu();

        $event = new PublicationStatusChangedEvent($cmd->objectId, ContentTranslation::class, $cmd->status);
        $logEntry = new Event($cmd->objectId, $cmd->userIdentifier, ContentTranslation::class, $event);

        $this->eventRepo->save($logEntry);
    }
}
