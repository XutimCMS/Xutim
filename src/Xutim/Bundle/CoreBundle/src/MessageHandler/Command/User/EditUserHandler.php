<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\User;

use Xutim\CoreBundle\Domain\Event\User\UserUpdatedEvent;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\CoreBundle\Message\Command\User\EditUserCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Repository\UserRepository;

readonly class EditUserHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private EventRepository $eventRepository
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

        $logEntry = new Event($user->getId(), $command->userIdentifier, User::class, $event);
        $this->eventRepository->save($logEntry, true);
    }
}
