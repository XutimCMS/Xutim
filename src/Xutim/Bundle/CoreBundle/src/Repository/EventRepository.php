<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Entity\Event;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return array<Event>
     */
    public function findByTranslation(ContentTranslation $translation): array
    {
        /** @var array<Event> */
        return $this->createQueryBuilder('event')
            ->where('event.objectId = :translationIdParam')
            ->setParameter('translationIdParam', $translation->getId())
            ->orderBy('event.recordedAt')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findLastByTranslation(ContentTranslation $translation): Event
    {
        /** @var ?Event $updateOrDeleteEvent */
        $updateOrDeleteEvent = $this->findOneBy(['objectId' => $translation], ['recordedAt' => 'desc']);
        if ($updateOrDeleteEvent !== null) {
            return $updateOrDeleteEvent;
        }

        /** @var Event */
        return $this->findOneBy(['objectId' => $translation->getObject()], ['recordedAt' => 'asc']);
    }

    public function eventsCountPerTranslation(ContentTranslation $translation): int
    {
        /** @var int */
        return $this->createQueryBuilder('event')
            ->select('COUNT(event.id)')
            ->where('event.objectId = :translationIdParam')
            ->setParameter('translationIdParam', $translation->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function save(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
