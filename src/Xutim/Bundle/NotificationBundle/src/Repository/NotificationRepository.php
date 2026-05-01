<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;
use Xutim\NotificationBundle\Domain\Model\NotificationInterface;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

/**
 * @extends ServiceEntityRepository<NotificationInterface>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @return list<NotificationInterface>
     */
    public function findLatestForRecipient(UserInterface $recipient, int $limit = 50): array
    {
        /** @var list<NotificationInterface> $notifications */
        $notifications = $this->createQueryBuilder('notification')
            ->where('notification.recipient = :recipient')
            ->setParameter('recipient', $recipient)
            ->orderBy('notification.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $notifications;
    }

    /**
     * @return list<NotificationInterface>
     */
    public function findUnreadForRecipient(UserInterface $recipient, int $limit = 50): array
    {
        /** @var list<NotificationInterface> $notifications */
        $notifications = $this->createQueryBuilder('notification')
            ->where('notification.recipient = :recipient')
            ->andWhere('notification.readAt IS NULL')
            ->setParameter('recipient', $recipient)
            ->orderBy('notification.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $notifications;
    }

    public function createForRecipientQueryBuilder(UserInterface $recipient): QueryBuilder
    {
        return $this->createQueryBuilder('notification')
            ->where('notification.recipient = :recipient')
            ->setParameter('recipient', $recipient)
            ->orderBy('notification.createdAt', 'DESC');
    }

    public function countUnreadForRecipient(UserInterface $recipient): int
    {
        /** @var int $count */
        $count = $this->createQueryBuilder('notification')
            ->select('COUNT(notification.id)')
            ->where('notification.recipient = :recipient')
            ->andWhere('notification.readAt IS NULL')
            ->setParameter('recipient', $recipient)
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    public function findOneForRecipient(Uuid|string $id, UserInterface $recipient): ?NotificationInterface
    {
        return $this->findOneBy(['id' => $id, 'recipient' => $recipient]);
    }

    public function findRecentByDeduplicationKey(string $deduplicationKey, \DateTimeImmutable $since): ?NotificationInterface
    {
        /** @var ?NotificationInterface $notification */
        $notification = $this->createQueryBuilder('notification')
            ->where('notification.deduplicationKey = :deduplicationKey')
            ->andWhere('notification.createdAt >= :since')
            ->setParameter('deduplicationKey', $deduplicationKey)
            ->setParameter('since', $since)
            ->orderBy('notification.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $notification;
    }

    public function hasDeduplicatedNotification(UserInterface $recipient, string $deduplicationKey): bool
    {
        /** @var int $count */
        $count = $this->createQueryBuilder('notification')
            ->select('COUNT(notification.id)')
            ->where('notification.recipient = :recipient')
            ->andWhere('notification.deduplicationKey = :deduplicationKey')
            ->andWhere('notification.readAt IS NULL')
            ->setParameter('recipient', $recipient)
            ->setParameter('deduplicationKey', $deduplicationKey)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function save(NotificationInterface $notification, bool $flush = false): void
    {
        $this->getEntityManager()->persist($notification);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
