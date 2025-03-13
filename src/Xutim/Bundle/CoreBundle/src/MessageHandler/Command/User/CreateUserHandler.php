<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\User;

use Jdenticon\Identicon;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Xutim\CoreBundle\Domain\Event\User\UserCreatedEvent;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Message\Command\User\CreateUserCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Repository\UserRepository;

readonly class CreateUserHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private EventRepository $eventRepository,
        private PasswordHasherFactoryInterface $passwordHasherFactory
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

        $user = new User(
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

        $logEntry = new Event($user->getId(), $command->userIdentifier, User::class, $event);
        $this->eventRepository->save($logEntry, true);
    }
}
