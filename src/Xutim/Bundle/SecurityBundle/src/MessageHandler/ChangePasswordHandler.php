<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\MessageHandler;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Exception\InvalidArgumentException;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\SecurityBundle\Message\ChangePasswordCommand;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;
use Xutim\SecurityBundle\Security\User;
use Xutim\SecurityBundle\Security\UserInterface;
use Xutim\SecurityComponent\Domain\Event\UserPasswordUpdatedEvent;

class ChangePasswordHandler implements CommandHandlerInterface
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

    public function __invoke(ChangePasswordCommand $command): void
    {
        $user = $this->userRepository->find($command->id);
        if ($user === null) {
            throw new InvalidArgumentException('User with ' . $command->id . ' id cannot be find.');
        }

        $user->changePassword($command->encodedPassword);
        $this->userRepository->save($user);

        $event = new UserPasswordUpdatedEvent($command->id, $command->encodedPassword);

        $logEntry = $this->logEventFactory->create($user->getId(), $user->getUserIdentifier(), User::class, $event);
        $this->eventRepository->save($logEntry, true);
    }
}
