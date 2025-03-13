<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Domain\Event\File\FileUploadedEvent;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Entity\FileTranslation;
use Xutim\CoreBundle\Message\Command\File\UploadFileMessage;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\FileTranslationRepository;

readonly class FileService
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

    public function createFile(?File $file): ?\Symfony\Component\HttpFoundation\File\File
    {
        if ($file === null) {
            return null;
        }

        $path = sprintf('%s%s', $this->fileUploader->getFilesPath(), $file->getFileName());

        return new \Symfony\Component\HttpFoundation\File\File($path, true);
    }

    public function persistFile(UploadFileMessage $cmd): File
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

        return $file;
    }

    private function isUniqueReference(string $ref): bool
    {
        $file = $this->fileRepository->findOneBy(['reference' => $ref]);

        return $file === null;
    }
}
