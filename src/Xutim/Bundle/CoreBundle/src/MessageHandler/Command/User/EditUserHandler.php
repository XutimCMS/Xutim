<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\User;

use Xutim\CoreBundle\Domain\Event\User\UserUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\CoreBundle\Message\Command\User\EditUserCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\UserRepository;

readonly class EditUserHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private UserRepository $userRepository,
        private LogEventRepository $eventRepository
    ) {
    }

    public function __invoke(EditUserCommand $command): void
    {
        /** @var User|null $user */
        $user = $this->userRepository->find($command->id);
        if ($user === null) {
            throw new LogicException('User couldn\'t be found');
        }
        $user->changeBasicInfo($command->name, $command->roles, $command->transLocales);
        $this->userRepository->save($user, true);

        $event = new UserUpdatedEvent($command->id, $command->name, $command->roles, $command->transLocales);

        $logEntry = $this->logEventFactory->create($user->getId(), $command->userIdentifier, User::class, $event);
        $this->eventRepository->save($logEntry, true);
    }
}
