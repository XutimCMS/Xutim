<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin;

use Webmozart\Assert\Assert;

readonly class FilterDto
{
    /**
     * @param int<0,max> $page
     * @param int<1,max> $pageLength
     */
    public function __construct(
        public string $searchTerm = '',
        public int $page = 1,
        public int $pageLength = 10,
        public string $orderColumn = '',
        public string $orderDirection = 'asc'
    ) {
        Assert::inArray($pageLength, [1, 4, 10, 12, 18, 20, 50, 100]);
        Assert::inArray($orderDirection, ['asc', 'desc']);
    }

    public function hasSearchTerm(): bool
    {
        return strlen(trim($this->searchTerm)) > 0;
    }

    public function hasOrder(): bool
    {
        return strlen(trim($this->orderColumn)) > 0;
    }

    public function getOrderDir(): string
    {
        return $this->orderDirection;
    }

    public function getReverseDirection(string $column): string
    {
        if ($column === $this->orderColumn) {
            return $this->orderDirection === 'asc' ? 'desc' : 'asc';
        }

        return 'asc';
    }
}
