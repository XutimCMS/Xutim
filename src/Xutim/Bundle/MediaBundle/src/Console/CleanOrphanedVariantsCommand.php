<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;

#[AsCommand(
    name: 'xutim:media:clean-orphaned-variants',
    description: 'Remove orphaned variant files that are no longer in the database.',
)]
final class CleanOrphanedVariantsCommand extends Command
{
    public function __construct(
        private readonly MediaVariantRepositoryInterface $variantRepository,
        private readonly StorageAdapterInterface $storage,
        private readonly string $publicDir,
        private readonly string $mediaPath,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Only show what would be deleted');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        $io->title('Cleaning Orphaned Media Variants');

        if ($dryRun === true) {
            $io->note('Running in dry-run mode - no files will be deleted.');
        }

        $variantsDir = $this->publicDir . '/' . $this->mediaPath . '/variants';

        if (!is_dir($variantsDir)) {
            $io->success('No variants directory found. Nothing to clean.');

            return Command::SUCCESS;
        }

        $finder = new Finder();
        $finder->files()->in($variantsDir);

        $knownPaths = $this->variantRepository->findAllPaths();
        $knownPathsSet = array_flip($knownPaths);

        $orphanedFiles = [];
        $orphanedSize = 0;

        foreach ($finder as $file) {
            $relativePath = 'variants/' . $file->getRelativePathname();

            if (!isset($knownPathsSet[$relativePath])) {
                $orphanedFiles[] = $relativePath;
                $orphanedSize += $file->getSize();
            }
        }

        if ($orphanedFiles === []) {
            $io->success('No orphaned files found.');

            return Command::SUCCESS;
        }

        $io->info(sprintf(
            'Found %d orphaned files (%s).',
            count($orphanedFiles),
            $this->formatBytes($orphanedSize),
        ));

        if ($dryRun === true) {
            $io->listing(array_slice($orphanedFiles, 0, 20));
            if (count($orphanedFiles) > 20) {
                $io->note(sprintf('... and %d more files', count($orphanedFiles) - 20));
            }

            return Command::SUCCESS;
        }

        $io->progressStart(count($orphanedFiles));
        $deletedCount = 0;

        foreach ($orphanedFiles as $path) {
            $this->storage->delete($path);
            $deletedCount++;
            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success(sprintf(
            'Deleted %d orphaned files (%s freed).',
            $deletedCount,
            $this->formatBytes($orphanedSize),
        ));

        return Command::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = 0;

        while ($bytes >= 1024 && $factor < count($units) - 1) {
            $bytes /= 1024;
            $factor++;
        }

        return round($bytes, 2) . ' ' . $units[$factor];
    }
}
