<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\MessageHandler;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Jdenticon\Identicon;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Xutim\BundleComponent\Domain\Event\UserCreatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\SecurityBundle\Domain\Factory\UserFactoryInterface;
use Xutim\SecurityBundle\Domain\Model\User;
use Xutim\SecurityBundle\Domain\Model\UserInterface;
use Xutim\SecurityBundle\Message\CreateUserCommand;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

readonly class CreateUserHandler implements CommandHandlerInterface
{
    /**
     * @param UserRepositoryInterface&ServiceEntityRepository<UserInterface> $userRepository
     */
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private UserRepositoryInterface $userRepository,
        private LogEventRepository $eventRepository,
        private PasswordHasherFactoryInterface $passwordHasherFactory,
        private UserFactoryInterface $userFactory
    ) {
    }

    public function __invoke(CreateUserCommand $command): void
    {
        $icon = new Identicon([
            'value' => $command->id->toRfc4122(),
        ]);
        $avatar = $icon->getImageDataUri('svg');

        $hasher = $this->passwordHasherFactory->getPasswordHasher(User::class);
        $hashedPassword = $hasher->hash($command->password);

        $user = $this->userFactory->create(
            $command->id,
            $command->email,
            $command->name,
            $hashedPassword,
            $command->roles,
            $command->transLocales,
            $avatar
        );
        $this->userRepository->save($user, true);

        $event = new UserCreatedEvent(
            $command->id,
            $command->email,
            $hashedPassword,
            $command->roles,
            $command->transLocales,
            $avatar
        );

        $logEntry = $this->logEventFactory->create($user->getId(), $command->userIdentifier, User::class, $event);
        $this->eventRepository->save($logEntry, true);
    }
}
