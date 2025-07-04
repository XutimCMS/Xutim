<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Console;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Xutim\SecurityBundle\Message\CreateUserCommand;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

final class CreateUserCliCommand extends Command
{
    protected static string $defaultName = 'xutim:user:create';

    public const string COMMAND_USER = 'console-command-user';

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserRepositoryInterface $userRepository
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The email of a user.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of a user.')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of a user.')
            ->addArgument(
                'roles',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Roles of a user.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var string $email */
        $email = $input->getArgument('email');
        /** @var string $name */
        $name = $input->getArgument('name');
        /** @var string $password */
        $password = $input->getArgument('password');
        /** @var list<string> $roles */
        $roles = $input->getArgument('roles');

        $command = new CreateUserCommand($email, $name, $password, $roles, [], self::COMMAND_USER);

        $this->commandBus->dispatch($command);

        $io->writeln('The user has been created.');

        return Command::SUCCESS;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $userRepository = $this->userRepository;

        $email = $io->ask('Email', null, function (string $answer) use ($userRepository) {
            if ($answer === '') {
                throw new RuntimeException('The email cannot be empty.');
            }
            if ($userRepository->findOneByEmail($answer) !== null) {
                throw new RuntimeException('The user with a given email already exists in a database.');
            }

            return $answer;
        });
        $name = $io->ask('Name', null, function (string $answer) use ($userRepository) {
            if ($answer === '') {
                throw new RuntimeException('The name cannot be empty.');
            }
            if ($userRepository->findOneByName($answer) !== null) {
                throw new RuntimeException('The user with a given name already exists in a database.');
            }

            return $answer;
        });
        $password = $io->askHidden('Password', function (string $answer) {
            if ($answer === '') {
                throw new RuntimeException('The password cannot be empty.');
            }

            if (strlen(trim($answer)) < 6) {
                throw new RuntimeException('The password should contain at least 6 characters.');
            }

            return $answer;
        });

        $roles = [];
        while ($io->confirm('Do you want to add more roles to this user?') === true) {
            $roles [] = $io->ask('Role', null, function (string $answer) {
                if ($answer === '') {
                    throw new RuntimeException('The role cannot be empty.');
                }

                return $answer;
            });
        }

        $input->setArgument('email', $email);
        $input->setArgument('name', $name);
        $input->setArgument('password', $password);
        $input->setArgument('roles', $roles);
    }
}
