<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin;

/**
 * @template T
 */
class FilteredResultDto
{
    public readonly int $totalPages;

    /**
     * @param int<0,max>              $currentPage
     * @param int<0,max>              $pageLength
     * @param int<0,max>              $resultLength
     * @param iterable<int|string, T> $filteredResult
     */
    public function __construct(
        public readonly int $currentPage,
        public readonly int $pageLength,
        public readonly int $resultLength,
        public readonly iterable $filteredResult
    ) {
        $this->totalPages = (int)ceil($this->resultLength / $this->pageLength);
    }

    /**
     * Get a first element's index that is showed on the current page.
     */
    public function getFirstElementOnCurrentPage(): int
    {
        if ($this->resultLength === 0) {
            return 0;
        }

        return ($this->currentPage * $this->pageLength) + 1;
    }

    /**
     * Get a last element's index that is showed on the current page.
     */
    public function getLastElementOnCurrentPage(): int
    {
        $lastElementPosition = ($this->currentPage * $this->pageLength) + $this->pageLength;
        if ($lastElementPosition > $this->resultLength) {
            return $this->resultLength;
        }

        return $lastElementPosition;
    }

    public function isOnFirstPage(): bool
    {
        return $this->currentPage === 0;
    }

    public function isOnLastPage(): bool
    {
        if ($this->totalPages === 0) {
            return true;
        }

        return $this->currentPage === $this->totalPages - 1;
    }

    /**
     * @return array<int> An ordered array
     */
    public function getPaginationRange(): array
    {
        if ($this->totalPages <= 1) {
            return range(0, $this->totalPages - 1);
        }

        $pagination = [];
        $maxLeftPages = 2;
        $maxRightPages = 2;

        if ($this->currentPage === 0) {
            $maxRightPages = 4;
        } elseif ($this->currentPage === 1) {
            $maxLeftPages = 1;
            $maxRightPages = 3;
        } elseif ($this->currentPage === $this->totalPages - 1) {
            $maxLeftPages = 4;
        } elseif ($this->currentPage === $this->totalPages - 2) {
            $maxLeftPages = 3;
            $maxRightPages = 1;
        }

        for ($i = $this->currentPage - $maxLeftPages; $i <= $this->currentPage + $maxRightPages; $i++) {
            if ($i >= 0 && $i < $this->totalPages) {
                $pagination[] = $i;
            }
        }

        return $pagination;
    }
}
