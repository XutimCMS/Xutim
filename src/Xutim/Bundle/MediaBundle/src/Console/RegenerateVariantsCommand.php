<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Console;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Domain\Model\MediaVariantInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
use Xutim\MediaBundle\Service\PresetRegistry;
use Xutim\MediaBundle\Service\VariantCleaner;
use Xutim\MediaBundle\Service\VariantGenerator;

#[AsCommand(
    name: 'xutim:media:regenerate-variants',
    description: 'Regenerate image variants for all or specific media files.',
)]
final class RegenerateVariantsCommand extends Command
{
    /**
     * @param class-string<MediaVariantInterface> $variantClass
     */
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaVariantRepositoryInterface $variantRepository,
        private readonly VariantGenerator $variantGenerator,
        private readonly VariantCleaner $variantCleaner,
        private readonly PresetRegistry $presetRegistry,
        private readonly EntityManagerInterface $em,
        private readonly string $variantClass,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('preset', 'p', InputOption::VALUE_OPTIONAL, 'Only regenerate specific preset')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force regeneration by cleaning existing variants first')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Max number of media items to process')
            ->addOption('offset', 'o', InputOption::VALUE_OPTIONAL, 'Skip first N media items', '0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $presetName = $input->getOption('preset');
        $force = (bool) $input->getOption('force');
        $limitOption = $input->getOption('limit');
        $limit = is_string($limitOption) ? (int) $limitOption : null;
        $offsetOption = $input->getOption('offset');
        $offset = is_string($offsetOption) ? (int) $offsetOption : 0;

        $preset = null;
        if (is_string($presetName)) {
            $preset = $this->presetRegistry->get($presetName);
            if ($preset === null) {
                $io->error(sprintf('Preset "%s" not found.', $presetName));

                return Command::FAILURE;
            }
        }

        $io->title('Regenerating Media Variants');

        if ($preset !== null) {
            $io->info(sprintf('Processing preset: %s', $preset->name));
        } else {
            $io->info('Processing all presets');
        }

        $allMediaItems = $this->mediaRepository->findAllImages();
        $mediaItems = array_slice($allMediaItems, $offset, $limit);
        $totalCount = count($mediaItems);

        if ($offset > 0 || $limit !== null) {
            $io->info(sprintf('Processing items %d–%d of %d total', $offset + 1, $offset + $totalCount, count($allMediaItems)));
        }
        $processedCount = 0;
        $variantsGenerated = 0;

        $io->progressStart($totalCount);

        foreach ($mediaItems as $index => $media) {
            if (!$this->em->contains($media)) {
                $media = $this->em->find($media::class, $media->id());
            }

            if ($media === null) {
                continue;
            }

            if ($force === true) {
                if ($preset !== null) {
                    $this->variantCleaner->cleanForPreset($media, $preset);
                } else {
                    $this->variantCleaner->cleanForMedia($media);
                    $this->variantRepository->deleteByMedia($media);
                }
            } elseif ($this->hasAllVariants($media, $preset?->name)) {
                $io->progressAdvance();
                continue;
            }

            if ($preset !== null) {
                $generatedVariants = $this->variantGenerator->generateForPreset($media, $preset);
            } else {
                $generatedVariants = $this->variantGenerator->generateAllPresets($media);
            }

            foreach ($generatedVariants as $generated) {
                $this->persistVariant($media, $generated);
            }

            $variantsGenerated += count($generatedVariants);
            $processedCount++;

            $this->em->flush();

            if (($index + 1) % 10 === 0) {
                $this->em->clear();
            }

            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->success(sprintf(
            'Processed %d media files, generated %d variants.',
            $processedCount,
            $variantsGenerated,
        ));

        return Command::SUCCESS;
    }

    private function hasAllVariants(MediaInterface $media, ?string $presetName): bool
    {
        $presets = $presetName !== null
            ? [$this->presetRegistry->get($presetName)]
            : array_values($this->presetRegistry->all());

        foreach ($presets as $preset) {
            if ($preset === null) {
                continue;
            }

            foreach ($preset->getEffectiveWidths() as $width) {
                foreach ($preset->formats as $format) {
                    $existing = $this->variantRepository->findByMediaPresetFormatWidth(
                        $media,
                        $preset->name,
                        $format,
                        $width,
                    );

                    if ($existing === null) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function persistVariant(
        MediaInterface $media,
        \Xutim\MediaBundle\Domain\Data\GeneratedVariant $generated,
    ): void {
        $existing = $this->variantRepository->findByMediaPresetFormatWidth(
            $media,
            $generated->preset,
            $generated->format,
            $generated->width,
        );

        if ($existing !== null) {
            return;
        }

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
}
