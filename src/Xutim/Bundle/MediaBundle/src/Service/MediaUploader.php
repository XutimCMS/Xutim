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
use Xutim\MediaBundle\Util\FileHasher;

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

        $now = new \DateTimeImmutable();
        $storagePath = sprintf(
            'uploads/%s/%s/%s.%s',
            $now->format('Y'),
            $now->format('m'),
            Uuid::v4()->toRfc4122(),
            $ext,
        );

        $meta = $this->writeAndAnalyze($file, $storagePath);

        /** @var MediaInterface $media */
        $media = new ($this->mediaClass)(
            $folder,
            $storagePath,
            $ext,
            $meta['mime'],
            $meta['hash'],
            $meta['sizeBytes'],
            $meta['width'],
            $meta['height'],
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

        $this->finalize($media, $storagePath);

        return $media;
    }

    public function replace(MediaInterface $media, UploadedFile $file): void
    {
        $ext = strtolower($file->getClientOriginalExtension() !== '' ? $file->getClientOriginalExtension() : ($file->guessExtension() ?? 'bin'));
        if ($ext !== $media->originalExt()) {
            throw new \InvalidArgumentException(sprintf(
                'Extension mismatch: expected "%s", got "%s".',
                $media->originalExt(),
                $ext,
            ));
        }

        $meta = $this->writeAndAnalyze($file, $media->originalPath());

        $media->replaceFile($meta['mime'], $meta['hash'], $meta['sizeBytes'], $meta['width'], $meta['height']);

        $this->finalize($media, $media->originalPath());
    }

    /**
     * Write file to storage and compute metadata (mime, size, hash, dimensions).
     *
     * @return array{mime: string, sizeBytes: int, hash: string, width: int, height: int}
     */
    private function writeAndAnalyze(UploadedFile $file, string $storagePath): array
    {
        $mime = $file->getMimeType() ?? 'application/octet-stream';
        $fileSize = $file->getSize();
        $sizeBytes = $fileSize !== false ? $fileSize : 0;

        $contents = file_get_contents($file->getPathname());
        if ($contents === false) {
            throw new \RuntimeException('Failed to read uploaded file');
        }

        $this->storage->write($storagePath, $contents);

        $isImage = str_starts_with($mime, 'image/');
        $absolutePath = $this->storage->absolutePath($storagePath);

        $width = 0;
        $height = 0;
        if ($isImage) {
            $size = getimagesize($absolutePath);
            if ($size !== false) {
                $width = $size[0];
                $height = $size[1];
            }
        }

        $hash = $isImage
            ? FileHasher::genereatePerceptualHash($absolutePath)
            : FileHasher::generateSHA256Hash($absolutePath);

        return [
            'mime' => $mime,
            'sizeBytes' => $sizeBytes,
            'hash' => $hash,
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * Generate blur hash (if image), flush, and dispatch variant regeneration.
     */
    private function finalize(MediaInterface $media, string $storagePath): void
    {
        $isImage = str_starts_with($media->mime(), 'image/');

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
    }
}
