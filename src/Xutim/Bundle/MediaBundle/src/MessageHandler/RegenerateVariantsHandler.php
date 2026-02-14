<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Xutim\MediaBundle\Domain\Model\MediaVariantInterface;
use Xutim\MediaBundle\Message\RegenerateVariantsMessage;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
use Xutim\MediaBundle\Service\VariantCleaner;
use Xutim\MediaBundle\Service\VariantGenerator;

#[AsMessageHandler]
final readonly class RegenerateVariantsHandler
{
    /**
     * @param class-string<MediaVariantInterface> $variantClass
     */
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private MediaVariantRepositoryInterface $variantRepository,
        private VariantGenerator $variantGenerator,
        private VariantCleaner $variantCleaner,
        private EntityManagerInterface $em,
        private string $variantClass,
    ) {
    }

    public function __invoke(RegenerateVariantsMessage $message): void
    {
        $media = $this->mediaRepository->findById($message->mediaId);
        if ($media === null) {
            return;
        }

        $this->variantCleaner->cleanForMedia($media);
        $this->variantRepository->deleteByMedia($media);

        $generatedVariants = $this->variantGenerator->generateAllPresets($media);
        foreach ($generatedVariants as $generated) {
            /** @var MediaVariantInterface $variant */
            $variant = new ($this->variantClass)(
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

        $this->em->flush();
    }
}
