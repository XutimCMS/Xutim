<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\AbstractUnicodeString;
use Symfony\Component\String\Slugger\SluggerInterface;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Entity\PublicationStatus;

/**
 * @extends ServiceEntityRepository<ContentTranslation>
 */
class ContentTranslationRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly SluggerInterface $slugger,
        private readonly SiteContext $siteContext
    ) {
        parent::__construct($registry, ContentTranslation::class);
    }

    public function findPublishedBySlug(string $slug, string $locale): ?ContentTranslation
    {
        /** @var ContentTranslation|null */
        return $this->createQueryBuilder('translation')
            ->where('translation.slug = :slugParam')
            ->andWhere('translation.locale = :localeParam')
            ->andWhere('translation.status = :publishedStatusParam')
            ->setParameter('slugParam', $slug)
            ->setParameter('localeParam', $locale)
            ->setParameter('publishedStatusParam', PublicationStatus::Published)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param array<int, string> $locales
     *
     * @return array<string>
     */
    public function filterMissingTranslationsByLocales(Page|Article $object, array $locales): array
    {
        $missingLocales = $this->findMissingTranslationLocales($object);

        return array_filter($missingLocales, fn (string $locale) => in_array($locale, $locales, true));
    }

    /**
     * @return array<string>
     */
    public function findMissingTranslationLocales(Page|Article $object): array
    {
        /** @var array{locale: string} $translatedLocales */
        $translatedLocales = $this->createQueryBuilder('trans')
            ->select('trans.locale')
            ->where($object instanceof Page ? 'trans.page = :objectParam' : 'trans.article = :objectParam')
            ->setParameter('objectParam', $object)
            ->getQuery()
            ->getSingleColumnResult();

        return array_filter(
            $this->siteContext->getLocales(),
            fn (string $locale) => !in_array($locale, $translatedLocales, true)
        );
    }


    /**
     * Generates a unique slug by adding a number at the end of the title when not unique.
     */
    public function generateUniqueSlugForTitle(
        string $title,
        string $locale,
        int $iteration = 0
    ): AbstractUnicodeString {
        $titleIteration = sprintf('%s%s', $title, $iteration === 0 ? '' : $iteration);
        $slug = $this->slugger->slug($titleIteration)->lower();

        if ($this->isSlugUnique($slug, $locale) === false) {
            $slug = $this->generateUniqueSlugForTitle($title, $locale, $iteration + 1);
        }

        return $slug;
    }

    public function isSlugUnique(AbstractUnicodeString $slug, string $locale, ?ContentTranslation $existingTrans = null): bool
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

    public function incrementVisits(ContentTranslation $translation): void
    {
        $query = $this->getEntityManager()->createQuery(
            'UPDATE Xutim\CoreBundle\\Entity\\ContentTranslation at 
             SET at.visits = at.visits + 1
             WHERE at.id = \'' . $translation->getId()->toRfc4122() . "'"
        );

        $query->execute();
    }

    public function save(ContentTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ContentTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
