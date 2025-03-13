<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\Page;

use Doctrine\ORM\EntityManagerInterface;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationCreatedEvent;
use Xutim\CoreBundle\Domain\Event\Page\PageCreatedEvent;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Message\Command\Page\CreatePageCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Service\FragmentsFileExtractor;

readonly class CreatePageHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PageRepository $pageRepository,
        private ContentTranslationRepository $contentTransRepo,
        private EventRepository $eventRepository,
        private FileRepository $fileRepository,
        private FragmentsFileExtractor $fragmentsFileExtractor
    ) {
    }

    public function __invoke(CreatePageCommand $cmd): void
    {
        $parentPage = $cmd->parentId !== null ? $this->pageRepository->find($cmd->parentId) : null;
        $page = new Page(
            $cmd->layout,
            $cmd->color,
            $cmd->locales,
            $cmd->preTitle,
            $cmd->title,
            $cmd->subTitle,
            $cmd->slug,
            $cmd->content,
            $cmd->defaultLanguage,
            $cmd->description,
            $parentPage
        );
        $translation = $page->getDefaultTranslation();

        $this->contentTransRepo->save($translation);
        $this->pageRepository->save($page, true);

        $this->connectFiles($cmd->content, $page);

        $pageCreatedEvent = new PageCreatedEvent(
            $page->getId(),
            $cmd->color,
            $cmd->locales,
            $translation->getId(),
            $page->getCreatedAt(),
            $cmd->parentId,
            $cmd->layout
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

        $logEntrySec = new Event(
            $page->getId(),
            $cmd->userIdentifier,
            Page::class,
            $pageCreatedEvent
        );
        $logEntryTrans = new Event(
            $translation->getId(),
            $cmd->userIdentifier,
            ContentTranslation::class,
            $translationCreatedEvent
        );

        $this->eventRepository->save($logEntrySec);
        $this->eventRepository->save($logEntryTrans, true);
    }

    /**
     * @param array{}|array{time: int, blocks: array{}|array{id: string, type: string, data: array<string, mixed>}, version: string} $content
     */
    private function connectFiles(array $content, Page $page): void
    {
        $files = $this->fragmentsFileExtractor->extractFiles($content);
        foreach ($files as $filename) {
            /** @var File|null $file */
            $file = $this->fileRepository->findOneBy(['dataPath' => $filename]);
            if ($file === null) {
                continue;
            }

            $file->addPage($page);
            $page->addFile($file);
        }
        $this->entityManager->flush();
    }
}
