<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\PublicationStatus;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Event\PublicationStatus\PublicationStatusChangedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Message\Command\PublicationStatus\ChangePublicationStatusCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;

readonly class ChangeStatusHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private EntityManagerInterface $entityManager,
        private ContentTranslationRepository $contentTransRepo,
        private LogEventRepository $eventRepo,
        private SiteContext $siteContext,
        private BlockContext $blockContext
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

        if ($trans->hasArticle()) {
            if ($cmd->status->isPublished()) {
                $trans->changePublishedAt(new DateTimeImmutable());
            }
        }
        $this->entityManager->flush();
        $this->siteContext->resetMenu();
        $this->blockContext->resetBlocksBelongsToContentTranslation($trans);

        $event = new PublicationStatusChangedEvent($cmd->objectId, ContentTranslation::class, $cmd->status);
        $logEntry = $this->logEventFactory->create($cmd->objectId, $cmd->userIdentifier, ContentTranslation::class, $event);

        $this->eventRepo->save($logEntry);
    }
}
