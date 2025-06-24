<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Factory\SnippetFactory;
use Xutim\CoreBundle\Entity\Site;
use Xutim\CoreBundle\Repository\SiteRepository;
use Xutim\CoreBundle\Repository\SnippetRepository;
use Xutim\CoreBundle\Routing\RouteSnippetRegistry;

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
        private readonly SnippetRepository $snippetRepo,
        private readonly SnippetFactory $snippetFactory
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
            $snippet = $this->snippetRepo->findOneBy(['code' => $route->snippetKey]);
            if ($snippet === null) {
                $snippet = $this->snippetFactory->create($route->snippetKey);
                $this->snippetRepo->save($snippet, true);
            }
        }

        $io->writeln('The app was successfully setup.');

        return Command::SUCCESS;
    }
}
