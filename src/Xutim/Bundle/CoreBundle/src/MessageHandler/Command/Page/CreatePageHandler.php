<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\Page;

use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationCreatedEvent;
use Xutim\CoreBundle\Domain\Event\Page\PageCreatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Domain\Factory\PageFactory;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Message\Command\Page\CreatePageCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Service\SearchContentBuilder;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;

readonly class CreatePageHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private PageRepository $pageRepository,
        private ContentTranslationRepository $contentTransRepo,
        private LogEventRepository $eventRepository,
        private MediaRepositoryInterface $mediaRepository,
        private SearchContentBuilder $searchContentBuilder,
        private PageFactory $pageFactory
    ) {
    }

    public function __invoke(CreatePageCommand $cmd): void
    {
        $file = null;
        if ($cmd->hasFeaturedImage() === true) {
            $file = $this->mediaRepository->findById($cmd->featuredImageId);
        }

        $parentPage = $cmd->parentId !== null ? $this->pageRepository->find($cmd->parentId) : null;
        $page = $this->pageFactory->create($cmd, $file, $parentPage);
        $translation = $page->getDefaultTranslation();

        $searchContent = $this->searchContentBuilder->build($translation);
        $searchTagContent = $this->searchContentBuilder->buildTagContent($translation);
        $translation->changeSearchContent($searchContent);
        $translation->changeSearchTagContent($searchTagContent);

        $this->contentTransRepo->save($translation);
        $this->pageRepository->save($page, true);

        $pageCreatedEvent = new PageCreatedEvent(
            $page->getId(),
            $cmd->locales,
            $translation->getId(),
            $page->getCreatedAt(),
            $cmd->parentId,
            $cmd->layout,
            $cmd->featuredImageId
        );

        $translationCreatedEvent = new ContentTranslationCreatedEvent(
            $translation->getId(),
            $cmd->preTitle,
            $cmd->title,
            $cmd->subTitle,
            $cmd->slug,
            $cmd->content,
            $cmd->defaultLanguage,
            $cmd->description,
            $translation->getCreatedAt(),
            $page->getId(),
            null
        );

        $logEntrySec = $this->logEventFactory->create(
            $page->getId(),
            $cmd->userIdentifier,
            Page::class,
            $pageCreatedEvent
        );
        $logEntryTrans = $this->logEventFactory->create(
            $translation->getId(),
            $cmd->userIdentifier,
            ContentTranslation::class,
            $translationCreatedEvent
        );

        $this->eventRepository->save($logEntrySec);
        $this->eventRepository->save($logEntryTrans, true);
    }
}
