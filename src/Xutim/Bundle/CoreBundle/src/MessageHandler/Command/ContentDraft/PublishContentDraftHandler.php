<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\ContentDraft;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Message\Command\ContentDraft\PublishContentDraftCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Service\SearchContentBuilder;

readonly class PublishContentDraftHandler implements CommandHandlerInterface
{
    public function __construct(
        private ContentDraftRepository $draftRepo,
        private ContentTranslationRepository $contentTransRepo,
        private LogEventFactory $logEventFactory,
        private LogEventRepository $eventRepository,
        private BlockContext $blockContext,
        private SiteContext $siteContext,
        private SearchContentBuilder $searchContentBuilder,
    ) {
    }

    public function __invoke(PublishContentDraftCommand $cmd): void
    {
        $draft = $this->draftRepo->find($cmd->draftId);
        if ($draft === null) {
            throw new NotFoundHttpException(sprintf(
                'Content draft "%s" could not be found',
                $cmd->draftId
            ));
        }

        $translation = $draft->getTranslation();

        $draft->applyToTranslation();

        $object = $translation->getObject();
        if ($translation->getId() === $object->getDefaultTranslation()->getId()) {
            foreach ($object->getTranslations() as $trans) {
                if ($trans->getId() === $object->getDefaultTranslation()->getId()) {
                    continue;
                }
                $trans->newTranslationChange();
            }
        }

        $searchContent = $this->searchContentBuilder->build($translation);
        $searchTagContent = $this->searchContentBuilder->buildTagContent($translation);
        $translation->changeSearchContent($searchContent);
        $translation->changeSearchTagContent($searchTagContent);

        $this->draftRepo->remove($draft);
        $this->contentTransRepo->save($translation);
        $this->draftRepo->flush();

        $this->siteContext->resetMenu();
        $this->blockContext->resetBlocksBelongsToContentTranslation($translation);

        $event = ContentTranslationUpdatedEvent::fromContentTranslation($translation);

        $log = $this->logEventFactory->create(
            $translation->getId(),
            $cmd->userIdentifier,
            ContentTranslation::class,
            $event
        );

        $this->eventRepository->save($log, true);
    }
}
