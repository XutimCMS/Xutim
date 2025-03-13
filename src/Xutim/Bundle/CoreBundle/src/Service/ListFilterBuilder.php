<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use LogicException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Dto\Admin\FilterDto;

class ListFilterBuilder
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    public function buildFilter(
        string $searchTerm,
        int $page,
        int $pageLength,
        string $orderColumn,
        string $orderDirection
    ): FilterDto {
        Assert::greaterThanEq($page, 0);
        Assert::inArray($pageLength, [10, 20, 50, 100]);
        Assert::inArray($orderDirection, ['asc', 'desc']);
        $filter = new FilterDto($searchTerm, $page, $pageLength, $orderColumn, $orderDirection);

        $errors = $this->validator->validate($filter);
        if (count($errors) > 0) {
            throw new LogicException('Invalid filter.');
        }

        return $filter;
    }
}
