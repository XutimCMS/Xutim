<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Entity\Site;
use Xutim\CoreBundle\Repository\SiteRepository;
use Xutim\SnippetBundle\Domain\Factory\SnippetFactoryInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetCategory;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;
use Xutim\SnippetBundle\Routing\RouteSnippetRegistry;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
#[AsCommand(
    name: 'xutim:app:init',
    description: 'Initialize a new site with default values.'
)]
class AppInitCommand extends Command
{
    public function __construct(
        private readonly SiteRepository $siteRepository,
        private readonly SiteContext $siteContext,
        private readonly SnippetRepositoryInterface $snippetRepo,
        private readonly SnippetFactoryInterface $snippetFactory
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $site = $this->siteRepository->findAll();
        if (count($site) === 0) {
            $site = new Site();
            $this->siteRepository->save($site, true);
            $this->siteContext->resetDefaultSite();
        }

        foreach (RouteSnippetRegistry::all() as $route) {
            $snippet = $this->snippetRepo->findByCode($route->snippetKey);
            if ($snippet === null) {
                $snippet = $this->snippetFactory->create($route->snippetKey, '', SnippetCategory::Route);
                $this->snippetRepo->save($snippet, true);
            }
        }

        $io->writeln('The app was successfully setup.');

        return Command::SUCCESS;
    }
}
