<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Util\LinkedList;

class LinkedListTest extends TestCase
{
    public function testEmptyList(): void
    {
        $list = new LinkedList([]);
        $this->assertTrue($list->isEmpty());
        $this->assertNull($list->getFirst());
    }

    public function testSimpleValues(): void
    {
        $list = new LinkedList([1]);
        $first = $list->getFirst();
        $this->assertEquals(1, $first->value);
        $this->assertTrue($first->isLast());
        $this->assertEquals(0, $first->index);
        $this->assertEquals($first, $list->getByIndex(0));

        $list = new LinkedList([1, 2]);
        $first = $list->getFirst();
        $this->assertEquals(1, $first->value);
        $this->assertFalse($first->isLast());
        $this->assertEquals(0, $first->index);
        $this->assertEquals($first, $list->getByIndex(0));

        $second = $first->next;
        $this->assertEquals(2, $second->value);
        $this->assertTrue($second->isLast());
        $this->assertEquals(1, $second->index);
        $this->assertEquals($second, $list->getByIndex(1));

        $list = new LinkedList([1, 2, 3]);
        $first = $list->getFirst();
        $this->assertEquals(1, $first->value);
        $this->assertFalse($first->isLast());
        $this->assertEquals(0, $first->index);
        $this->assertEquals($first, $list->getByIndex(0));
        $second = $first->next;
        $this->assertEquals(2, $second->value);
        $this->assertFalse($second->isLast());
        $this->assertEquals(1, $second->index);
        $this->assertEquals($second, $list->getByIndex(1));
        $third = $second->next;
        $this->assertEquals(3, $third->value);
        $this->assertTrue($third->isLast());
        $this->assertEquals(2, $third->index);
        $this->assertEquals($third, $list->getByIndex(2));
    }
}
