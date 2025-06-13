<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\AbstractUnicodeString;
use Symfony\Component\String\Slugger\SluggerInterface;
use Xutim\CoreBundle\Domain\Model\TagTranslationInterface;

/**
 * @extends ServiceEntityRepository<TagTranslationInterface>
 */
class TagTranslationRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
        private readonly SluggerInterface $slugger,
    ) {
        parent::__construct($registry, $entityClass);
    }

    public function save(TagTranslationInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TagTranslationInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Generates a unique slug by adding a number at the end of the title when not unique.
     */
    public function generateUniqueSlugForName(
        string $name,
        string $locale,
        int $iteration = 0
    ): AbstractUnicodeString {
        $nameIteration = sprintf('%s%s', $name, $iteration === 0 ? '' : $iteration);
        $slug = $this->slugger->slug($nameIteration)->lower();

        if ($this->isSlugUnique($slug, $locale) === false) {
            $slug = $this->generateUniqueSlugForName($name, $locale, $iteration + 1);
        }

        return $slug;
    }

    public function isSlugUnique(AbstractUnicodeString $slug, string $locale, ?TagTranslationInterface $existingTrans = null): bool
    {
        $translations = $this->findBy(['slug' => $slug->toString(), 'locale' => $locale]);
        if (count($translations) === 0) {
            return true;
        }

        if ($existingTrans !== null) {
            return count($translations) === 1 && $translations[0]->getId()->equals($existingTrans->getId());
        }

        return false;
    }
}
