<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Entity\PublicationStatus;

/**
 * @extends  ServiceEntityRepository<PageInterface>
 */
class PageRepository extends ServiceEntityRepository
{
    public const FILTER_ORDER_COLUMN_MAP = [
        'id' => 'page.id',
        'title' => 'translation.title',
        'updatedAt' => 'translation.updatedAt',
        'publicationStatus' => 'translation.status',
    ];

    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
        private readonly string $contentTranslationEntityClass,
        private readonly ContentContext $contentContext,
        #[Autowire('%kernel.default_locale%')]
        private readonly string $defaultLocale,
    ) {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @return array{
     *      roots: array<string>,
     *      pages: array<string, array{page: PageInterface, translation: ContentTranslationInterface,children: list<string>}>
     * }
     */
    public function hierarchyByPublished(string $locale, bool $archived = false, bool $onlyTranslated = false): array
    {
        $builder = $this->createQueryBuilder('node');
        $builder
            ->leftJoin('node.parent', 'parent')
            ->addSelect('parent')
            ->leftJoin('node.defaultTranslation', 'transDef')
            ->addSelect('transDef')
            ->addOrderBy('parent.id', 'ASC')
            ->addOrderBy('node.position', 'ASC');

        if ($onlyTranslated === true) {
            $builder
                ->leftJoin('node.translations', 'transLoc')
                ->andWhere('transLoc.locale = :locale')
                ->addSelect('transLoc')
                ->setParameter('locale', $locale, Types::STRING);
        }

        if ($archived === false) {
            $builder->andWhere('node.archived = :archivedParam')
                ->setParameter('archivedParam', false);
        }

        /** @var array<PageInterface> */
        $pages = $builder->getQuery()->getResult();

        // Pre-fetch translations for the pages.
        if ($onlyTranslated === false) {
            if ($pages !== []) {
                $this->getEntityManager()
                    ->createQuery(<<<DQL
                        SELECT t, p
                        FROM {$this->contentTranslationEntityClass} t
                        JOIN t.page p
                        WHERE p IN (:pages) AND t.locale = :locale
                    DQL)
                    ->setParameter('pages', $pages)
                    ->setParameter('locale', $locale)
                    ->getResult();
            }
        }


        $rootPagesIds = [];
        $pagesMap = [];

        foreach ($pages as $page) {
            $pageId = $page->getId()->toRfc4122();
            if ($page->getParent() === null) {
                $rootPagesIds[] = $pageId;
            }

            $localeTranslation = $page->getTranslations()->get($locale);
            $trans = $localeTranslation ?? $page->getDefaultTranslation();

            $pagesMap[$pageId] = [
                'page' => $page,
                'translation' => $trans,
                'children' => []
            ];
        }

        foreach ($pages as $page) {
            if ($page->getParent() !== null) {
                $childId = $page->getId()->toRfc4122();
                $parentId = $page->getParent()->getId()->toRfc4122();
                if (array_key_exists($parentId, $pagesMap)) {
                    $pagesMap[$parentId]['children'][] = $childId;
                }
            }
        }

        /** @var array{roots: array<string>, pages: array<string, array{page: PageInterface, translation: ContentTranslationInterface, children: list<string>}>} */
        return [
            'roots' => $rootPagesIds,
            'pages' => $pagesMap,
        ];
    }

    public function queryByFilter(FilterDto $filter, ?string $locale = null): QueryBuilder
    {
        $builder = $this->createQueryBuilder('page')
            ->select('page')
            ->leftJoin('page.translations', 'translation', 'WITH', 'translation.locale = :localeParam')
            ->leftJoin('page.translations', 'fallbackTranslation', 'WITH', 'fallbackTranslation.locale = :fallbackLocale')
            ->leftJoin('page.defaultTranslation', 'defaultTranslation')
            ->leftJoin('page.parent', 'parent')
            ->leftJoin('parent.defaultTranslation', 'parentDefaultTranslation')
            ->andWhere('page.archived = false')
            ->setParameter('localeParam', $locale)
            ->setParameter('fallbackLocale', $this->defaultLocale);

        if ($filter->hasSearchTerm() === true) {
            $builder
                ->andWhere($builder->expr()->orX(
                    $builder->expr()->like('LOWER(CASE WHEN translation.id IS NOT NULL THEN translation.title WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.title ELSE defaultTranslation.title END)', ':searchTerm'),
                    $builder->expr()->like('LOWER(CASE WHEN translation.id IS NOT NULL THEN translation.slug WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.slug ELSE defaultTranslation.slug END)', ':searchTerm'),
                ))
                ->setParameter('searchTerm', '%' . strtolower($filter->searchTerm) . '%');
        }

        $hasOrder = in_array($filter->orderColumn, array_keys(self::FILTER_ORDER_COLUMN_MAP), true);

        if ($filter->orderColumn === 'updatedAt') {
            $builder
                ->addOrderBy(
                    'CASE
                        WHEN translation.id IS NOT NULL THEN translation.updatedAt
                        WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.updatedAt
                        ELSE defaultTranslation.updatedAt
                     END',
                    $filter->getOrderDir()
                );
        } elseif ($hasOrder === false) {
            $builder
                ->addOrderBy(
                    'CASE
                        WHEN translation.id IS NOT NULL THEN translation.updatedAt
                        WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.updatedAt
                        ELSE defaultTranslation.updatedAt
                     END',
                    'desc'
                );
        } else {
            $builder->orderBy(
                self::FILTER_ORDER_COLUMN_MAP[$filter->orderColumn],
                $filter->getOrderDir()
            );
        }

        if ($filter->hasCol('title')) {
            /** @var string $title */
            $title = $filter->col('title');
            $builder
                ->andWhere(
                    $builder->expr()->like('LOWER(CASE WHEN translation.id IS NOT NULL THEN translation.title WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.title ELSE defaultTranslation.title END)', ':colTitle')
                )
                ->setParameter('colTitle', sprintf('%%%s%%', strtolower($title)));
        }

        if ($filter->hasCol('publicationStatus')) {
            /** @var string $status */
            $status = $filter->col('publicationStatus');
            $builder
                ->andWhere(
                    'CASE
                        WHEN translation.id IS NOT NULL THEN translation.status
                        WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.status
                        ELSE defaultTranslation.status
                     END = :colStatus'
                )
                ->setParameter('colStatus', $status);
        }

        if ($filter->hasCol('translationStatus')) {
            /** @var string $translationStatus */
            $translationStatus = $filter->col('translationStatus');

            if ($translationStatus === 'translated') {
                $builder->andWhere('translation.id IS NOT NULL');
            } elseif ($translationStatus === 'missing') {
                $builder->andWhere('translation.id IS NULL')
                    ->andWhere(
                        $builder->expr()->orX(
                            $builder->expr()->eq('page.allTranslationLocales', 'true'),
                            $builder->expr()->like('CAST(page.translationLocales AS TEXT)', ':localeFilterMissing')
                        )
                    )
                    ->setParameter('localeFilterMissing', '%' . $locale . '%');
            }
        }

        if ($filter->hasCol('updatedAt')) {
            /** @var string $updatedAtRange */
            $updatedAtRange = $filter->col('updatedAt');
            $now = new \DateTimeImmutable();
            $updatedAtCase =
                'CASE
                    WHEN translation.id IS NOT NULL THEN translation.updatedAt
                    WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.updatedAt
                    ELSE defaultTranslation.updatedAt
                 END';

            if ($updatedAtRange === '90+') {
                $builder
                    ->andWhere($updatedAtCase . ' < :updatedBefore')
                    ->setParameter('updatedBefore', $now->modify('-90 days'));
            } elseif (ctype_digit($updatedAtRange)) {
                $builder
                    ->andWhere($updatedAtCase . ' >= :updatedSince')
                    ->setParameter('updatedSince', $now->modify("-{$updatedAtRange} days"));
            }
        }

        return $builder;
    }

    public function findRootPage(): PageInterface
    {
        /** @var PageInterface $root */
        $root = $this->createQueryBuilder('page')
            ->where('page.parent IS NULL')
            ->andWhere('trans.slug = :rootSlugParam')
            ->leftJoin('page.translations', 'trans')
            ->setParameter('rootSlugParam', 'root-page')
            ->getQuery()
            ->getSingleResult();

        return $root;
    }

    /**
     * Check if setting a new parent would create a loop.
     */
    public function wouldCreateLoop(PageInterface $page, ?PageInterface $newParent): bool
    {
        if ($newParent === null) {
            return false;
        }
        $currentParent = $newParent;

        while ($currentParent !== null) {
            if ($currentParent->getId()->equals($page->getId()) === true) {
                return true;
            }
            $currentParent = $currentParent->getParent();
        }

        return false;
    }

    /**
     * @return array<PageInterface>
     */
    public function getPathHydrated(PageInterface $page): array
    {
        $path = [];
        $current = $page;

        while ($current) {
            $path[] = $current;
            $current = $current->getParent();
        }

        return array_reverse($path);
    }

    public function getPath(PageInterface $page, string $locale): string
    {
        $pages = $this->getPathHydrated($page);
        $path = array_map(fn (PageInterface $page)
            => $page->getTranslationByLocaleOrDefault($locale)->getTitle(), $pages);

        return implode(' / ', $path);
    }

    /**
     * @return array<string,string>
     */
    public function findAllPaths(?PageInterface $currentPage = null): array
    {
        $locale = $this->contentContext->getLanguage();
        $builder = $this->createQueryBuilder('page');
        /** @var array<PageInterface> $pages */
        $pages = $builder
            ->orderBy('page.parent', 'desc')
            ->addOrderBy('page.position', 'asc')
            ->getQuery()
            ->getResult();

        $paths = [];
        foreach ($pages as $page) {
            // Check if there could be a loop. Very slow check.
            if ($currentPage === null ||
                $this->wouldCreateLoop($currentPage, $page) === false
            ) {
                $paths[$page->getId()->toRfc4122()] = $this->getPath($page, $locale);
            }
        }

        return $paths;
    }

    /**
     * @param array<string> $locales
     */
    public function countTranslatedTranslations(PageInterface $page, ?array $locales): int
    {
        $builder = $this->createQueryBuilder('page')
            ->select('COUNT(trans.id)')
            ->leftJoin('page.translations', 'trans')
            ->where('page = :pageParam')
            ->andWhere('trans.status = :status')
            ->setParameter('pageParam', $page)
            ->setParameter('status', PublicationStatus::Published);
        if ($locales !== null) {
            $builder
                ->andWhere('trans.locale in (:locales)')
                ->setParameter('locales', $locales);
        }

        /** @var int $translatedTotal */
        $translatedTotal = $builder
            ->getQuery()
            ->getSingleScalarResult();

        return $translatedTotal;
    }

    /**
     * @param list<string> $locales
     */
    public function countUnpublishedForLocales(array $locales): int
    {
        if ($locales === []) {
            return 0;
        }

        /** @var int $count */
        $count = $this->createQueryBuilder('page')
            ->select('COUNT(DISTINCT page.id)')
            ->innerJoin('page.translations', 'trans')
            ->where('trans.locale IN (:locales)')
            ->andWhere('trans.status != :publishedStatus')
            ->setParameter('locales', $locales)
            ->setParameter('publishedStatus', PublicationStatus::Published)
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    /**
     * @param list<string> $locales
     */
    public function countUntranslatedForLocales(array $locales): int
    {
        if ($locales === []) {
            return 0;
        }

        $qb = $this->createQueryBuilder('page');
        $qb
            ->select('page.id')
            ->leftJoin('page.translations', 'translation', 'WITH', $qb->expr()->in('translation.locale', ':locales'))
            ->groupBy('page.id')
            ->having(
                $qb->expr()->orX(
                    $qb->expr()->eq($qb->expr()->count('translation.id'), 0),
                    $qb->expr()->lt($qb->expr()->countDistinct('translation.locale'), ':localeCount')
                )
            )
            ->setParameter('locales', $locales)
            ->setParameter('localeCount', count($locales));

        $localeConditions = [$qb->expr()->eq('page.allTranslationLocales', 'true')];
        foreach ($locales as $i => $locale) {
            $param = 'localeFilter' . $i;
            $localeConditions[] = $qb->expr()->like('CAST(page.translationLocales AS TEXT)', ':' . $param);
            $qb->setParameter($param, '%' . $locale . '%');
        }
        $qb->andWhere($qb->expr()->orX(...$localeConditions));

        return count($qb->getQuery()->getResult());
    }

    public function moveUp(PageInterface $page, int $step = 1): void
    {
        $page->movePosUp($step);
    }

    public function moveDown(PageInterface $page, int $step = 1): void
    {
        $page->movePosDown($step);
    }

    public function save(PageInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PageInterface $entity, bool $flush = false): void
    {
        if ($entity->canBeDeleted() === false) {
            // We can't delete a node with children and other relations.
            return;
        }
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
