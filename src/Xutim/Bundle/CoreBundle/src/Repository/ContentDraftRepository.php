<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Domain\Model\ContentDraftInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Entity\DraftStatus;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

/**
 * @extends ServiceEntityRepository<ContentDraftInterface>
 */
class ContentDraftRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
    ) {
        parent::__construct($registry, $entityClass);
    }

    public function findDraft(ContentTranslationInterface $translation): ?ContentDraftInterface
    {
        /** @var ContentDraftInterface|null $draft */
        $draft = $this->createQueryBuilder('d')
            ->where('d.translation = :translation')
            ->andWhere('d.status = :status')
            ->setParameter('translation', $translation)
            ->setParameter('status', DraftStatus::EDITING)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $draft;
    }

    public function findLiveDraft(ContentTranslationInterface $translation): ?ContentDraftInterface
    {
        /** @var ContentDraftInterface|null $draft */
        $draft = $this->createQueryBuilder('d')
            ->where('d.translation = :translation')
            ->andWhere('d.status = :status')
            ->setParameter('translation', $translation)
            ->setParameter('status', DraftStatus::LIVE)
            ->getQuery()
            ->getOneOrNullResult();

        return $draft;
    }

    public function findUserDraft(
        ContentTranslationInterface $translation,
        UserInterface $user,
    ): ?ContentDraftInterface {
        /** @var ContentDraftInterface|null $draft */
        $draft = $this->createQueryBuilder('d')
            ->where('d.translation = :translation')
            ->andWhere('d.user = :user')
            ->andWhere('d.status IN (:statuses)')
            ->setParameter('translation', $translation)
            ->setParameter('user', $user)
            ->setParameter('statuses', [DraftStatus::EDITING, DraftStatus::STALE])
            ->orderBy('d.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $draft;
    }

    /**
     * @return list<ContentDraftInterface>
     */
    public function findEditingDrafts(ContentTranslationInterface $translation): array
    {
        /** @var list<ContentDraftInterface> $drafts */
        $drafts = $this->createQueryBuilder('d')
            ->where('d.translation = :translation')
            ->andWhere('d.status = :status')
            ->setParameter('translation', $translation)
            ->setParameter('status', DraftStatus::EDITING)
            ->getQuery()
            ->getResult();

        return $drafts;
    }

    /**
     * @return list<ContentDraftInterface>
     */
    public function findStaleDrafts(ContentTranslationInterface $translation): array
    {
        /** @var list<ContentDraftInterface> $drafts */
        $drafts = $this->createQueryBuilder('d')
            ->where('d.translation = :translation')
            ->andWhere('d.status = :status')
            ->setParameter('translation', $translation)
            ->setParameter('status', DraftStatus::STALE)
            ->getQuery()
            ->getResult();

        return $drafts;
    }

    public function save(ContentDraftInterface $draft, bool $flush = false): void
    {
        $this->getEntityManager()->persist($draft);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ContentDraftInterface $draft, bool $flush = false): void
    {
        $this->getEntityManager()->remove($draft);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
