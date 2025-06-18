<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\RedirectBundle\Domain\Model\CmsRedirectInterface;

interface RedirectRepositoryInterface
{
    public function queryByFilter(FilterDto $filter): QueryBuilder;

    public function find(mixed $id): ?CmsRedirectInterface;

    public function save(CmsRedirectInterface $entity, bool $andFlush = false): void;

    public function remove(CmsRedirectInterface $entity, bool $andFlush = false): void;
}
