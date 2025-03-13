<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\User;

use Xutim\CoreBundle\Domain\Event\User\UserDeletedEvent;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Exception\InvalidArgumentException;
use Xutim\CoreBundle\Message\Command\User\DeleteUserCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Repository\UserRepository;

class UserDeleteHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EventRepository $eventRepository
    ) {
    }

    public function __invoke(DeleteUserCommand $command): void
    {
        $user = $this->userRepository->find($command->id);
        if ($user === null) {
            throw new InvalidArgumentException('User not found');
        }

        $this->userRepository->remove($user, true);

        $event = new UserDeletedEvent($command->id);

        $logEntry = new Event($user->getId(), $command->userIdentifier, User::class, $event);
        $this->eventRepository->save($logEntry, true);
    }
}
