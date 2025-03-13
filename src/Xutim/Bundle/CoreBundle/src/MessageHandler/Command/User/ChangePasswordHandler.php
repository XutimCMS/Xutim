<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\User;

use Xutim\CoreBundle\Domain\Event\User\UserPasswordUpdatedEvent;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Exception\InvalidArgumentException;
use Xutim\CoreBundle\Message\Command\User\ChangePasswordCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Repository\UserRepository;

readonly class ChangePasswordHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private EventRepository $eventRepository
    ) {
    }

    public function __invoke(ChangePasswordCommand $command): void
    {
        $user = $this->userRepository->find($command->id);
        if ($user === null) {
            throw new InvalidArgumentException('User with ' . $command->id . ' id cannot be find.');
        }

        $user->changePassword($command->encodedPassword);
        $this->userRepository->save($user);

        $event = new UserPasswordUpdatedEvent($command->id, $command->encodedPassword);

        $logEntry = new Event($user->getId(), $user->getUserIdentifier(), User::class, $event);
        $this->eventRepository->save($logEntry, true);
    }
}
