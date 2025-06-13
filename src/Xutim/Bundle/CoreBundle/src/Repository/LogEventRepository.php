<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\LogEventInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Domain\Model\TagTranslationInterface;

/**
 * @extends ServiceEntityRepository<LogEventInterface>
 */
class LogEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @return array<LogEventInterface>
     */
    public function findByTranslation(ContentTranslationInterface $translation): array
    {
        /** @var array<LogEventInterface> */
        return $this->createQueryBuilder('event')
            ->where('event.objectId = :translationIdParam')
            ->setParameter('translationIdParam', $translation->getId())
            ->orderBy('event.recordedAt')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findLastByTranslation(ContentTranslationInterface|TagTranslationInterface $translation): LogEventInterface
    {
        /** @var ?LogEventInterface $updateOrDeleteEvent */
        $updateOrDeleteEvent = $this->findOneBy(['objectId' => $translation], ['recordedAt' => 'desc']);
        if ($updateOrDeleteEvent !== null) {
            return $updateOrDeleteEvent;
        }

        /** @var LogEventInterface */
        return $this->findOneBy(['objectId' => $translation->getObject()], ['recordedAt' => 'asc']);
    }

    public function findFirstByObject(FileInterface|ArticleInterface|PageInterface|TagInterface $object): LogEventInterface
    {
        /** @var LogEventInterface */
        return $this->findOneBy(['objectId' => $object->getId()], ['recordedAt' => 'asc']);
    }

    public function eventsCountPerTranslation(ContentTranslationInterface|TagTranslationInterface $translation): int
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

    public function save(LogEventInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LogEventInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
