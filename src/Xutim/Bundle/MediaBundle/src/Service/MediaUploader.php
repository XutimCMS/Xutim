<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Domain\Model\MediaTranslationInterface;
use Xutim\MediaBundle\Domain\Model\MediaVariantInterface;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaTranslationRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;

final class MediaUploader
{
    /**
     * @param class-string<MediaInterface>            $mediaClass
     * @param class-string<MediaTranslationInterface> $mediaTranslationClass
     * @param class-string<MediaVariantInterface>     $mediaVariantClass
     */
    public function __construct(
        private readonly StorageAdapterInterface $storage,
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaTranslationRepositoryInterface $translationRepository,
        private readonly MediaVariantRepositoryInterface $variantRepository,
        private readonly VariantGenerator $variantGenerator,
        private readonly BlurHashGenerator $blurHashGenerator,
        private readonly string $mediaClass,
        private readonly string $mediaTranslationClass,
        private readonly string $mediaVariantClass,
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

            $generatedVariants = $this->variantGenerator->generateAllPresets($media);
            foreach ($generatedVariants as $generated) {
                /** @var MediaVariantInterface $variant */
                $variant = new ($this->mediaVariantClass)(
                    $media,
                    $generated->preset,
                    $generated->format,
                    $generated->width,
                    $generated->height,
                    $generated->path,
                    $generated->fingerprint,
                );
                $this->variantRepository->save($variant);
            }
        }

        $this->mediaRepository->save($media, true);

        return $media;
    }
}
