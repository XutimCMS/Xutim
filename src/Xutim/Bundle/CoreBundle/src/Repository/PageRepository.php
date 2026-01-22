<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Entity\PublicationStatus;

/**
 * @extends  ServiceEntityRepository<PageInterface>
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
        private readonly string $contentTranslationEntityClass,
        private readonly ContentContext $contentContext
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
