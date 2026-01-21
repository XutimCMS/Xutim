<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin;

use Webmozart\Assert\Assert;

readonly class FilterDto
{
    /**
     * @param int<0,max>                         $page
     * @param int<1,max>                         $pageLength
     * @param array<string, string|list<string>> $cols
     */
    public function __construct(
        public string $searchTerm = '',
        public int $page = 1,
        public int $pageLength = 10,
        public string $orderColumn = '',
        public string $orderDirection = 'asc',
        public array $cols = []
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

    public function hasCol(string $key): bool
    {
        if (!array_key_exists($key, $this->cols)) {
            return false;
        }

        $value = $this->cols[$key];

        if (is_array($value)) {
            return count(array_filter($value, static fn ($v): bool => $v !== '')) > 0;
        }

        return $value !== '';
    }

    /**
     * Get column filter value as string (for single-value filters).
     */
    public function col(string $key): ?string
    {
        $value = $this->cols[$key] ?? null;

        if (is_array($value)) {
            return $value[0] ?? null;
        }

        return $value;
    }

    /**
     * Get column filter value as array (for multi-value filters like multiselect).
     *
     * @return list<string>
     */
    public function colArray(string $key): array
    {
        $value = $this->cols[$key] ?? [];

        if (is_string($value)) {
            return $value !== '' ? [$value] : [];
        }

        /** @var list<string> */
        return array_values(array_filter($value, static fn ($v): bool => $v !== ''));
    }
}
