<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Security;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class OnlineUserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserStorage $userStorage,
        private readonly CacheInterface $onlineUsersCache
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'logOnlineUserInfo'
        ];
    }

    public function logOnlineUserInfo(ControllerEvent $event): void
    {
        $user = $this->userStorage->getUser();
        if ($user === null) {
            return;
        }

        $id = $user->getId()->toRfc4122();

        $this->onlineUsersCache->delete($id);
        $this->onlineUsersCache->get($id, function (ItemInterface $item) use ($user) {
            return [
                'id' => $user->getId()->toRfc4122(),
                'username' => $user->getUserIdentifier(),
                'lastAction' => new \DateTimeImmutable()
            ];
        });
    }
}
