<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Xutim\CoreBundle\Sitemap\SitemapGenerator;

#[AsCommand(
    name: 'xutim:core:generate-sitemap',
    description: 'Generates sitemap.xml file.'
)]
final class GenerateSitemapCommand extends Command
{
    protected static string $defaultName = 'xutim:core:generate-sitemap';

    public function __construct(
        private readonly SitemapGenerator $sitemapGenerator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->sitemapGenerator->generate();

        $io->writeln('sitemap.xml file was successfully generated.');

        return Command::SUCCESS;
    }
}
