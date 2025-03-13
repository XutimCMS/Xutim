<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\File;

use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Domain\Event\File\FileUploadedEvent;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Entity\FileTranslation;
use Xutim\CoreBundle\Message\Command\File\UploadFileMessage;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\FileTranslationRepository;
use Xutim\CoreBundle\Service\FileUploader;
use Xutim\CoreBundle\Service\RandomStringGenerator;

readonly class UploadFileHandler implements CommandHandlerInterface
{
    public function __construct(
        private FileUploader $fileUploader,
        private FileRepository $fileRepository,
        private FileTranslationRepository $fileTranslationRepository,
        private EventRepository $eventRepository,
        private ContentContext $contentContext,
        private RandomStringGenerator $randomStringGenerator,
    ) {
    }

    // TODO: Use FileService here.
    public function __invoke(UploadFileMessage $cmd): void
    {
        $filePath = $this->fileUploader->upload($cmd->file, $cmd->id);

        do {
            $ref = $this->randomStringGenerator->generateRandomString(3);
        } while ($this->isUniqueReference($ref) === false);

        $file = new File(
            $cmd->id,
            $cmd->name !== '' ? $cmd->name : $cmd->file->getClientOriginalName(),
            $cmd->alt,
            $cmd->locale !== '' ? $cmd->locale : $this->contentContext->getLanguage(),
            $filePath,
            $cmd->file->getClientOriginalExtension(),
            $ref,
            $cmd->article,
            $cmd->page
        );
        /** @var FileTranslation $translation */
        $translation = $file->getTranslations()->first();

        $this->fileTranslationRepository->save($translation);
        $this->fileRepository->save($file, true);

        $fileUploadedEvent = new FileUploadedEvent(
            $file->getId(),
            $file->getFileName(),
            $translation->getName()
        );
        $logEntry = new Event($file->getId(), $cmd->userIdentifier, File::class, $fileUploadedEvent);
        $this->eventRepository->save($logEntry, true);
    }

    private function isUniqueReference(string $ref): bool
    {
        $file = $this->fileRepository->findOneBy(['reference' => $ref]);

        return $file === null;
    }
}
