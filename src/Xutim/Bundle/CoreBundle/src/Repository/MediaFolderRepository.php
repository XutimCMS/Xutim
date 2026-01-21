<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Domain\Model\MediaFolderInterface;
use Xutim\CoreBundle\Dto\Admin\FilterDto;

/**
 * @extends ServiceEntityRepository<MediaFolderInterface>
 */
class MediaFolderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @return array<int, MediaFolderInterface>
     */
    public function findByParent(MediaFolderInterface $folder): array
    {
        /** @var array<int, MediaFolderInterface> */
        return $this->createQueryBuilder('folder')
            ->where('folder.parent = :parent')
            ->setParameter('parent', $folder)
            ->getQuery()
            ->getResult()
        ;
    }

    public function queryByFilterAndFolder(FilterDto $filter, ?MediaFolderInterface $parent): QueryBuilder
    {
        $builder = $this->createQueryBuilder('folder')
            ->select('folder', 'children', 'file')
            ->leftJoin('folder.children', 'children')
            ->leftJoin('folder.files', 'file')
            ->distinct()
            ->orderBy('folder.name', 'asc')
        ;
        
        if ($filter->hasSearchTerm() === true) {
            $terms = preg_split('/\s+/', trim($filter->searchTerm), -1, PREG_SPLIT_NO_EMPTY);
            if ($terms !== false) {
                foreach ($terms as $idx => $term) {
                    $paramName = sprintf('term%s', $idx);

                    $builder
                        ->andWhere(
                            $builder->expr()->like('LOWER(folder.name)', ':' . $paramName),
                        )
                        ->setParameter($paramName, '%' . strtolower($term) . '%');
                }
            }
        }

        if ($parent === null) {
            return $builder
                ->andWhere('folder.parent IS NULL')
            ;
        }

        return $builder
            ->innerJoin('folder.parent', 'parent')
            ->andWhere('parent = :parent')
            ->setParameter('parent', $parent)
        ;
    }

    /**
     * @return array<int, MediaFolderInterface>
    */
    public function findByParentFolder(?MediaFolderInterface $parent): array
    {
        $filter = new FilterDto();
        /** @var array<int, MediaFolderInterface> */
        return $this
            ->queryByFilterAndFolder($filter, $parent)
            ->getQuery()
            ->getResult();
    }

    public function save(MediaFolderInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MediaFolderInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Returns all folders in hierarchical order: root folders alphabetically,
     * followed by each folder's children alphabetically (depth-first).
     *
     * @return array<int, MediaFolderInterface>
     */
    public function findAllOrderedHierarchically(): array
    {
        $allFolders = $this->findBy([], ['name' => 'asc']);

        $foldersByParent = [];
        foreach ($allFolders as $folder) {
            $parentId = $folder->getParent()?->getId()?->toRfc4122() ?? 'root';
            $foldersByParent[$parentId] ??= [];
            $foldersByParent[$parentId][] = $folder;
        }

        return $this->addFoldersRecursively($foldersByParent, 'root');
    }

    /**
     * @param  array<string, array<int, MediaFolderInterface>> $foldersByParent
     * @return array<int, MediaFolderInterface>
     */
    private function addFoldersRecursively(
        array $foldersByParent,
        string $parentId
    ): array {
        if (!isset($foldersByParent[$parentId])) {
            return [];
        }

        $result = [];
        foreach ($foldersByParent[$parentId] as $folder) {
            $result[] = $folder;
            $childFolders = $this->addFoldersRecursively(
                $foldersByParent,
                $folder->getId()->toRfc4122()
            );
            $result = array_merge($result, $childFolders);
        }

        return $result;
    }
}
