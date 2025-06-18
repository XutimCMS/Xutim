<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Xutim\CoreBundle\Domain\Model\UserInterface;
use Xutim\CoreBundle\Message\Command\Article\PublishArticlesCommand;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
#[AsCommand(
    name: 'xutim:content:publish-scheduled',
    description: 'Publish scheduled content.'
)]
final class PublishScheduledContentCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $command = new PublishArticlesCommand(UserInterface::COMMAND_USER);

        $this->commandBus->dispatch($command);

        $io->writeln('All scheduled and ready for publication articles have been published.');

        return Command::SUCCESS;
    }
}
