<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\Article;

use Symfony\Component\Messenger\MessageBusInterface;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\CoreBundle\Message\Command\Article\PublishScheduledArticlesCommand;
use Xutim\CoreBundle\Message\Command\PublicationStatus\ChangePublicationStatusCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;

readonly class PublishArticlesHandler implements CommandHandlerInterface
{
    public function __construct(
        private ContentTranslationRepository $contentTransRepo,
        private MessageBusInterface $commandBus,
    ) {
    }

    public function __invoke(PublishScheduledArticlesCommand $cmd): void
    {
        $translations = $this->contentTransRepo->findReadyForPublicationArticles();
        foreach ($translations as $trans) {
            $command = new ChangePublicationStatusCommand(
                $trans->getId(),
                PublicationStatus::Published,
                $cmd->userIdentifier
            );
            $this->commandBus->dispatch($command);
        }
    }
}
