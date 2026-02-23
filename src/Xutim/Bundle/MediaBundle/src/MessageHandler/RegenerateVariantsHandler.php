<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Xutim\MediaBundle\Domain\Model\MediaVariantInterface;
use Xutim\MediaBundle\Message\RegenerateVariantsMessage;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
use Xutim\MediaBundle\Service\PresetRegistry;
use Xutim\MediaBundle\Service\VariantCleaner;
use Xutim\MediaBundle\Service\VariantGenerator;
use Xutim\MediaBundle\Service\VariantPathResolver;

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
        private ?HubInterface $hub,
        private PresetRegistry $presetRegistry,
        private VariantPathResolver $pathResolver,
        private LoggerInterface $logger,
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

        $mediaId = $media->id()->toRfc4122();
        $presets = $this->presetRegistry->all();
        $totalPresets = count($presets);
        $presetIndex = 0;
        $totalVariants = 0;

        foreach ($presets as $preset) {
            $generatedVariants = $this->variantGenerator->generateForPreset($media, $preset);
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

            $totalVariants += count($generatedVariants);
            ++$presetIndex;

            $this->publishProgress($mediaId, [
                'type' => 'preset_complete',
                'preset' => $preset->name,
                'presetIndex' => $presetIndex,
                'totalPresets' => $totalPresets,
            ]);
        }

        $this->em->flush();

        $thumbnailUrl = null;
        $thumbVariants = $this->variantRepository->findByMediaPresetFormat($media, 'thumb_small', 'jpg');
        if ($thumbVariants !== []) {
            $thumbnailUrl = $this->pathResolver->getUrl($thumbVariants[0]);
        }

        $this->publishProgress($mediaId, [
            'type' => 'complete',
            'totalVariants' => $totalVariants,
            'thumbnailUrl' => $thumbnailUrl,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function publishProgress(string $mediaId, array $data): void
    {
        if ($this->hub === null) {
            return;
        }

        try {
            $this->hub->publish(new Update(
                'media/' . $mediaId . '/variants',
                json_encode($data, JSON_THROW_ON_ERROR),
            ));
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to publish Mercure variant progress: {message}', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
