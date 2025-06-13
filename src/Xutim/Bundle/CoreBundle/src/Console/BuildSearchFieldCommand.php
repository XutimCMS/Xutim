<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Console;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Service\SearchContentBuilder;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
#[AsCommand(
    name: 'xutim:core:build-search-fields',
    description: 'Build a search fields for content translation for fulltext search.'
)]
final class BuildSearchFieldCommand extends Command
{
    protected static string $defaultName = 'xutim:core:build-search-fields';

    public function __construct(
        private readonly ContentTranslationRepository $transRepo,
        private readonly SearchContentBuilder $searchContentBuilder,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $counter = 1;
        foreach ($this->transRepo->findAll() as $trans) {
            $searchContent = $this->searchContentBuilder->build($trans);
            $searchTagContent = $this->searchContentBuilder->buildTagContent($trans);
            $trans->changeSearchContent($searchContent);
            $trans->changeSearchTagContent($searchTagContent);
            if ($counter++ % 20 === 0) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();

        $io->writeln('The search content was successfully created for all objects.');

        return Command::SUCCESS;
    }
}
