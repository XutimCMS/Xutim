<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Domain\Model\MediaTranslationInterface;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Message\RegenerateVariantsMessage;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaTranslationRepositoryInterface;

final class MediaUploader
{
    /**
     * @param class-string<MediaInterface>            $mediaClass
     * @param class-string<MediaTranslationInterface> $mediaTranslationClass
     */
    public function __construct(
        private readonly StorageAdapterInterface $storage,
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaTranslationRepositoryInterface $translationRepository,
        private readonly BlurHashGenerator $blurHashGenerator,
        private readonly MessageBusInterface $messageBus,
        private readonly string $mediaClass,
        private readonly string $mediaTranslationClass,
    ) {
    }

    public function upload(
        UploadedFile $file,
        string $name,
        string $alt,
        string $locale,
        ?string $copyright = null,
        ?MediaFolderInterface $folder = null,
    ): MediaInterface {
        $originalExt = $file->getClientOriginalExtension();
        $ext = strtolower($originalExt !== '' ? $originalExt : ($file->guessExtension() ?? 'bin'));
        $mime = $file->getMimeType() ?? 'application/octet-stream';
        $fileSize = $file->getSize();
        $sizeBytes = $fileSize !== false ? $fileSize : 0;

        $now = new \DateTimeImmutable();
        $storagePath = sprintf(
            'uploads/%s/%s/%s.%s',
            $now->format('Y'),
            $now->format('m'),
            Uuid::v4()->toRfc4122(),
            $ext,
        );

        $contents = file_get_contents($file->getPathname());
        if ($contents === false) {
            throw new \RuntimeException('Failed to read uploaded file');
        }

        $this->storage->write($storagePath, $contents);

        $width = 0;
        $height = 0;
        $isImage = str_starts_with($mime, 'image/');

        if ($isImage) {
            $absolutePath = $this->storage->absolutePath($storagePath);
            $size = getimagesize($absolutePath);
            if ($size !== false) {
                $width = $size[0];
                $height = $size[1];
            }
        }

        $hash = hash('sha256', $contents);

        /** @var MediaInterface $media */
        $media = new ($this->mediaClass)(
            $folder,
            $storagePath,
            $ext,
            $mime,
            $hash,
            $sizeBytes,
            $width,
            $height,
            $copyright,
        );

        $this->mediaRepository->save($media);

        /** @var MediaTranslationInterface $translation */
        $translation = new ($this->mediaTranslationClass)(
            $media,
            $locale,
            $name,
            $alt,
        );
        $media->addTranslation($translation);
        $this->translationRepository->save($translation);

        if ($isImage) {
            $blurHash = $this->blurHashGenerator->generate($storagePath);
            if ($blurHash !== null) {
                $media->changeBlurHash($blurHash);
            }
        }

        $this->mediaRepository->save($media, true);

        if ($isImage) {
            $this->messageBus->dispatch(new RegenerateVariantsMessage($media->id()));
        }

        return $media;
    }
}
