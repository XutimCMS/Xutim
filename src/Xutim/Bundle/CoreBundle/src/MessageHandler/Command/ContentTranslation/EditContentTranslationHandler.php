<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\ContentTranslation;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Message\Command\ContentTranslation\EditContentTranslationCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Service\SearchContentBuilder;

readonly class EditContentTranslationHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private ContentTranslationRepository $contentTransRepo,
        private LogEventRepository $eventRepository,
        private BlockContext $blockContext,
        private SiteContext $siteContext,
        private SearchContentBuilder $searchContentBuilder
    ) {
    }

    public function __invoke(EditContentTranslationCommand $cmd): void
    {
        $translation = $this->contentTransRepo->find($cmd->translationId);
        if ($translation === null) {
            throw new NotFoundHttpException(sprintf(
                'Content translation "%s" could not be found',
                $cmd->translationId
            ));
        }

        // Set a untranslated_change status to all other translations.
        $object = $translation->getObject();
        if ($translation->getId() === $object->getDefaultTranslation()->getId() &&
            (
                $translation->getPreTitle() !== $cmd->preTitle ||
                $translation->getTitle() !== $cmd->title ||
                $translation->getSubTitle() !== $cmd->subTitle ||
                $translation->getContent() !== $cmd->content ||
                $translation->getDescription() !== $cmd->description
            )
        ) {
            foreach ($object->getTranslations() as $trans) {
                if ($trans->getId() === $object->getDefaultTranslation()->getId()) {
                    continue;
                }
                $trans->newTranslationChange();
            }
        }
        $translation->change(
            $cmd->preTitle,
            $cmd->title,
            $cmd->subTitle,
            $cmd->slug,
            $cmd->content,
            $cmd->locale,
            $cmd->description
        );

        $searchContent = $this->searchContentBuilder->build($translation);
        $searchTagContent = $this->searchContentBuilder->buildTagContent($translation);
        $translation->changeSearchContent($searchContent);
        $translation->changeSearchTagContent($searchTagContent);

        $this->contentTransRepo->save($translation, true);
        $this->siteContext->resetMenu();
        $this->blockContext->resetBlocksBelongsToContentTranslation($translation);

        $event = new ContentTranslationUpdatedEvent(
            $translation->getId(),
            $cmd->preTitle,
            $cmd->title,
            $cmd->subTitle,
            $cmd->slug,
            $cmd->content,
            $cmd->description,
            $cmd->locale,
            $translation->getCreatedAt()
        );

        $log = $this->logEventFactory->create(
            $translation->getId(),
            $cmd->userIdentifier,
            ContentTranslation::class,
            $event
        );

        $this->eventRepository->save($log, true);
    }
}
