<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\RedirectBundle\Domain\Model\RedirectInterface;

interface RedirectRepositoryInterface
{
    public function queryByFilter(FilterDto $filter): QueryBuilder;

    public function findById(mixed $id): ?RedirectInterface;

    /**
     * @return array<int, RedirectInterface>
    */
    public function findAll(): array;

    public function save(RedirectInterface $entity, bool $andFlush = false): void;

    public function remove(RedirectInterface $entity, bool $andFlush = false): void;
}
