<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Admin\Dto;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Dto\Admin\FilterDto;

class FilterDtoTest extends TestCase
{
    public function testConstruct(): void
    {
        $dto = new FilterDto('search', 3, 20, 'createdAt', 'asc');
        $this->assertEquals('search', $dto->searchTerm);
        $this->assertEquals(3, $dto->page);
        $this->assertEquals(20, $dto->pageLength);
        $this->assertEquals('createdAt', $dto->orderColumn);
        $this->assertEquals('asc', $dto->orderDirection);
    }

    public function testHasSearchTerm(): void
    {
        $dto = new FilterDto('search', 0, 20, 'createdAt', 'desc');
        $this->assertEquals(true, $dto->hasSearchTerm());
        $dto = new FilterDto('', 0, 20, 'createdAt', 'desc');
        $this->assertEquals(false, $dto->hasSearchTerm());
    }

    public function testHasOrder(): void
    {
        $dto = new FilterDto('search', 0, 20, 'createdAt', 'desc');
        $this->assertEquals(true, $dto->hasOrder());
        $dto = new FilterDto('', 0, 20, '', 'desc');
        $this->assertEquals(false, $dto->hasOrder());
    }

    public function testGetReverseDirection(): void
    {
        $dto = new FilterDto('search', 0, 20, 'createdAt', 'desc');
        $this->assertEquals('asc', $dto->getReverseDirection('createdAt'));
        $this->assertEquals('asc', $dto->getReverseDirection('title'));
        $dto = new FilterDto('search', 0, 20, 'createdAt', 'asc');
        $this->assertEquals('desc', $dto->getReverseDirection('createdAt'));
    }
}
