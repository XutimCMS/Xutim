<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Domain\Event\File\FileUploadedEvent;
use Xutim\CoreBundle\Domain\Factory\FileFactory;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\FileTranslationInterface;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Message\Command\File\UploadFileMessage;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\FileTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Util\FileHasher;

readonly class FileService
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private FileUploader $fileUploader,
        private FileRepository $fileRepository,
        private FileTranslationRepository $fileTranslationRepository,
        private LogEventRepository $eventRepository,
        private ContentContext $contentContext,
        private RandomStringGenerator $randomStringGenerator,
        private FileFactory $fileFactory
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

    public function persistFile(UploadFileMessage $cmd): FileInterface
    {
        $isImage = $this->isImage($cmd->file);
        $filePath = $this->fileUploader->upload($cmd->file, $cmd->id);

        do {
            $ref = $this->randomStringGenerator->generateRandomString(3);
        } while ($this->isUniqueReference($ref) === false);

        $path = sprintf('%s%s', $this->fileUploader->getFilesPath(), $filePath);
        if ($isImage === true) {
            $hash = FileHasher::genereatePerceptualHash($path);
        } else {
            $hash = FileHasher::generateSHA256Hash($path);
        }

        $similarFile = $this->fileRepository->findOneBy(['hash' => $hash]);
        if ($similarFile === null) {
            $file = $this->fileFactory->create(
                $cmd->id,
                $cmd->name !== '' ? $cmd->name : $cmd->file->getClientOriginalName(),
                $cmd->alt,
                $cmd->locale !== '' ? $cmd->locale : $this->contentContext->getLanguage(),
                $filePath,
                $cmd->file->getClientOriginalExtension(),
                $ref,
                $hash,
                $cmd->copyright,
                $cmd->article,
                $cmd->page
            );
            /** @var FileTranslationInterface $translation */
            $translation = $file->getTranslations()->first();

            $this->fileTranslationRepository->save($translation);
            $this->fileRepository->save($file, true);

            $fileUploadedEvent = new FileUploadedEvent(
                $file->getId(),
                $file->getFileName(),
                $translation->getName()
            );
            $logEntry = $this->logEventFactory->create($file->getId(), $cmd->userIdentifier, File::class, $fileUploadedEvent);
            $this->eventRepository->save($logEntry, true);

            return $file;
        }

        return $similarFile;
    }

    private function isUniqueReference(string $ref): bool
    {
        $file = $this->fileRepository->findOneBy(['reference' => $ref]);

        return $file === null;
    }

    private function isImage(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();
        if ($mimeType === null) {
            return false;
        }

        return str_starts_with($mimeType, 'image/');
    }
}
