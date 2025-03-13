<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Admin\Dto;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Dto\Admin\FilteredResultDto;

class FilteredResultDtoTest extends TestCase
{
    public function testConstruct(): void
    {
        $dto = new FilteredResultDto(1, 20, 100, range(200, 299));
        $this->assertEquals(1, $dto->currentPage);
        $this->assertEquals(20, $dto->pageLength);
        $this->assertEquals(100, $dto->resultLength);
        $this->assertCount(100, $dto->filteredResult);
        $this->assertEquals(5, $dto->totalPages);
        $this->assertEquals(21, $dto->getFirstElementOnCurrentPage());
        $this->assertEquals(40, $dto->getLastElementOnCurrentPage());
    }

    /**
     * @dataProvider firstAndLastPageProvider
     */
    public function testOnFirstAndLastPage(int $currentPage, bool $onFirst, bool $onLast): void
    {
        $dto = new FilteredResultDto($currentPage, 20, 100, [0, 2]);
        $this->assertEquals($onFirst, $dto->isOnFirstPage());
        $this->assertEquals($onLast, $dto->isOnLastPage());
    }

    /**
     * @dataProvider paginationRangeProvider
     * @param int<0,max> $currentPage
     * @param int<0,max> $pageLength
     * @param int<0,max> $resultLength
     * @param array<int> $expected
     */
    public function testPaginationRange(
        int $currentPage,
        int $pageLength,
        int $resultLength,
        array $expected
    ): void {
        $dto = new FilteredResultDto($currentPage, $pageLength, $resultLength, [0, 1]);
        $this->assertEquals($expected, $dto->getPaginationRange());
    }

    public function firstAndLastPageProvider(): array
    {
        return [
            [0, true, false],
            [1, false, false],
            [3, false, false],
            [4, false, true]
        ];
    }

    public function paginationRangeProvider(): array
    {
        return [
            [0, 1, 1, [0]],
            [0, 1, 2, [0, 1]],
            [0, 1, 3, [0, 1, 2]],
            [0, 1, 4, [0, 1, 2, 3]],
            [0, 1, 5, [0, 1, 2, 3, 4]],
            [4, 1, 5, [0, 1, 2, 3, 4]],
            [3, 1, 5, [0, 1, 2, 3, 4]],
            [2, 1, 5, [0, 1, 2, 3, 4]],
            [1, 1, 5, [0, 1, 2, 3, 4]],
            [5, 1, 10, [3, 4, 5, 6, 7]],
        ];
    }
}
