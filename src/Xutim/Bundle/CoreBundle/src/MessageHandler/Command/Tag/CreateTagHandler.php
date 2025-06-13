<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\Tag;

use Xutim\CoreBundle\Domain\Event\Tag\TagCreatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Domain\Factory\TagFactory;
use Xutim\CoreBundle\Entity\Tag;
use Xutim\CoreBundle\Message\Command\Tag\CreateTagCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Repository\TagTranslationRepository;

readonly class CreateTagHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private TagRepository $tagRepo,
        private TagTranslationRepository $tagTransRepo,
        private LogEventRepository $eventRepository,
        private FileRepository $fileRepository,
        private TagFactory $tagFactory
    ) {
    }

    public function __invoke(CreateTagCommand $cmd): void
    {
        $file = null;
        if ($cmd->hasFeaturedImage() === true) {
            $file = $this->fileRepository->find($cmd->featuredImageId);
        }

        $tag = $this->tagFactory->create(
            $cmd->name,
            $cmd->slug,
            $cmd->defaultLanguage,
            $cmd->color,
            $file,
            $cmd->layout
        );
        $translation = $tag->getTranslationByLocaleOrAny($cmd->defaultLanguage);

        $this->tagRepo->save($tag);
        $this->tagTransRepo->save($translation, true);


        $event = new TagCreatedEvent(
            $tag->getId(),
            $translation->getId(),
            $cmd->name,
            $cmd->slug,
            $cmd->defaultLanguage,
            $cmd->color->getValueOrDefaultHex(),
            $tag->getFeaturedImage()?->getId()
        );
        $logEntry = $this->logEventFactory->create($tag->getId(), $cmd->userIdentifier, Tag::class, $event);
        $this->eventRepository->save($logEntry, true);
    }
}
