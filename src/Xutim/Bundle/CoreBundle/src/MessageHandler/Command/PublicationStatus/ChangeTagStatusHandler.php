<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\PublicationStatus;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Domain\Event\PublicationStatus\PublicationStatusChangedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\Tag;
use Xutim\CoreBundle\Message\Command\PublicationStatus\ChangeTagPublicationStatusCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\TagRepository;

readonly class ChangeTagStatusHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private EntityManagerInterface $entityManager,
        private TagRepository $tagRepo,
        private LogEventRepository $eventRepo,
        private BlockContext $blockContext
    ) {
    }

    public function __invoke(ChangeTagPublicationStatusCommand $cmd): void
    {
        $tag = $this->tagRepo->find($cmd->objectId);

        if ($tag === null) {
            throw new NotFoundHttpException(sprintf(
                'The given tag with id: "%s" could not be found',
                $cmd->objectId
            ));
        }

        if ($tag->isInStatus($cmd->status)) {
            return;
        }

        $tag->changeStatus($cmd->status);
        $this->entityManager->flush();
        $this->blockContext->resetBlocksBelongsToTag($tag);

        $event = new PublicationStatusChangedEvent($cmd->objectId, Tag::class, $cmd->status);
        $logEntry = $this->logEventFactory->create($cmd->objectId, $cmd->userIdentifier, Tag::class, $event);

        $this->eventRepo->save($logEntry);
    }
}
