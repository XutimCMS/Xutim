<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\MessageHandler;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\SecurityBundle\Domain\Event\UserUpdatedEvent;
use Xutim\SecurityBundle\Domain\Model\User;
use Xutim\SecurityBundle\Domain\Model\UserInterface;
use Xutim\SecurityBundle\Message\EditUserCommand;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

readonly class EditUserHandler implements CommandHandlerInterface
{
    /**
     * @param UserRepositoryInterface&ServiceEntityRepository<UserInterface> $userRepository
     */
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private UserRepositoryInterface $userRepository,
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
