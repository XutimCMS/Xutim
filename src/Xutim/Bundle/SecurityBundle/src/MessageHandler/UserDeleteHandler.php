<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\MessageHandler;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Exception\InvalidArgumentException;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\SecurityBundle\Message\DeleteUserCommand;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;
use Xutim\SecurityBundle\Security\User;
use Xutim\SecurityBundle\Security\UserInterface;
use Xutim\SecurityComponent\Domain\Event\UserDeletedEvent;

class UserDeleteHandler implements CommandHandlerInterface
{
    /**
     * @param UserRepositoryInterface&ServiceEntityRepository<UserInterface> $userRepository
     */
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private readonly UserRepositoryInterface $userRepository,
        private readonly LogEventRepository $eventRepository
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

        $logEntry = $this->logEventFactory->create($user->getId(), $command->userIdentifier, User::class, $event);
        $this->eventRepository->save($logEntry, true);
    }
}
