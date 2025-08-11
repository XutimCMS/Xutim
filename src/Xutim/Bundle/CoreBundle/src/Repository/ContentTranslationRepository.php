<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\AbstractUnicodeString;
use Symfony\Component\String\Slugger\SluggerInterface;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\CoreBundle\Util\TsVectorLanguageMapper;

/**
 * @extends ServiceEntityRepository<ContentTranslationInterface>
 */
class ContentTranslationRepository extends ServiceEntityRepository
{
    private readonly string $tableName;

    public function __construct(
        ManagerRegistry $registry,
        private readonly string $entityClass,
        private readonly SluggerInterface $slugger,
        private readonly SiteContext $siteContext
    ) {
        parent::__construct($registry, $entityClass);

        $em = $registry->getManagerForClass($entityClass);
        assert($em instanceof EntityManagerInterface); // safety check

        $this->tableName = $em->getClassMetadata($entityClass)->getTableName();
    }

    /**
     * @return array{0: array<int, ContentTranslationInterface>, 1: int, 2: int}
     */
    public function queryByFilter(FilterDto $filter, string $locale): array
    {
        $dictionary = TsVectorLanguageMapper::getDictionary($locale);
        $term = strtolower($filter->searchTerm);

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata($this->entityClass, 'ct');

        $sql = <<<SQL
            SELECT *,
                ts_rank(search_vector, plainto_tsquery(:dictionary, :term)) AS rank
            FROM {$this->tableName} ct
            WHERE ct.locale = :locale
              AND ct.search_vector @@ plainto_tsquery(:dictionary, :term)
              AND ct.status = :publishedStatus
            ORDER BY rank DESC
            LIMIT :limit OFFSET :offset
        SQL;
        $params = [
            'dictionary' => $dictionary,
            'term' => $term,
            'locale' => $locale,
            'publishedStatus' => PublicationStatus::Published->value,
        ];

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameters($params);
        $query->setParameter('limit', $filter->pageLength);
        $query->setParameter('offset', ($filter->page - 1) * $filter->pageLength);
        /** @var array<int, ContentTranslationInterface> $result */
        $result = $query->getResult();

        $countQuery = $this->getEntityManager()->getConnection()->prepare(<<<SQL
            SELECT COUNT(*) FROM {$this->tableName}
            WHERE locale = :locale
              AND search_vector @@ plainto_tsquery(:dictionary, :term)
              AND status = :publishedStatus
        SQL);
        $countQuery->bindValue('dictionary', $params['dictionary']);
        $countQuery->bindValue('term', $params['term']);
        $countQuery->bindValue('locale', $params['locale']);
        $countQuery->bindValue('publishedStatus', $params['publishedStatus']);
        /** @var int $total */
        $total = $countQuery->executeQuery()->fetchOne();

        return [
            $result,
            $total,
            (int)ceil($total / $filter->pageLength),
        ];
    }

    public function findPublishedBySlug(string $slug, string $locale): ?ContentTranslationInterface
    {
        /** @var ContentTranslationInterface|null */
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
    public function filterMissingTranslationsByLocales(PageInterface|ArticleInterface $object, array $locales): array
    {
        $missingLocales = $this->findMissingTranslationLocales($object);

        return array_filter($missingLocales, fn (string $locale) => in_array($locale, $locales, true));
    }

    /**
     * @return array<string>
     */
    public function findMissingTranslationLocales(PageInterface|ArticleInterface $object): array
    {
        /** @var array{locale: string} $translatedLocales */
        $translatedLocales = $this->createQueryBuilder('trans')
            ->select('trans.locale')
            ->where($object instanceof PageInterface ? 'trans.page = :objectParam' : 'trans.article = :objectParam')
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

    public function isSlugUnique(AbstractUnicodeString $slug, string $locale, ?ContentTranslationInterface $existingTrans = null): bool
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

    /**
     * @return array<int, ContentTranslationInterface>
     */
    public function findReadyForPublicationArticles(): array
    {
        $builder = $this->createQueryBuilder('trans');

        /** @var array<int, ContentTranslationInterface> */
        $translations = $builder
            ->innerJoin('trans.article', 'article')
            ->where('trans.status = :status')
            ->andWhere('article.scheduledAt <= :now')
            ->setParameter('status', PublicationStatus::Scheduled)
            ->setParameter('now', new DateTimeImmutable())
            ->getQuery()
            ->getResult()
        ;

        return $translations;
    }

    public function save(ContentTranslationInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ContentTranslationInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
