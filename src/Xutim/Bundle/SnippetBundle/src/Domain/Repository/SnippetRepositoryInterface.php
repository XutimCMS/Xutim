<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\SnippetBundle\Domain\Model\SnippetCategory;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

interface SnippetRepositoryInterface
{
    /**
     * @return array<int, SnippetInterface>
     */
    public function findByCategory(SnippetCategory $category): array;

    public function findByCode(string $code): ?SnippetInterface;

    public function findById(mixed $id): ?SnippetInterface;

    public function queryByFilter(FilterDto $filter, string $locale = 'en'): QueryBuilder;

    public function save(SnippetInterface $entity, bool $flush = false): void;

    public function remove(SnippetInterface $entity, bool $flush = false): void;
}
