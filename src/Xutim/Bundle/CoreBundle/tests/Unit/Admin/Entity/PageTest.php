<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Admin\Entity;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Entity\Page;

final class PageTest extends TestCase
{
    /**
     * @param list<string> $locales
     */
    #[DataProvider('changeDataProvider')]
    public function testChange(?string $color, array $locales, ?Page $parent): void
    {
        $page = new Page(null, [], null, null);
        $page->change($color, $locales, $parent);
        $this->assertEquals($locales, $page->getLocales());
        $this->assertEquals($parent, $page->getParent());
    }

    /**
     * @return iterable<string, array{?string, list<string>, ?Page}>
     */
    public static function changeDataProvider(): iterable
    {
        $parent = new Page(null, ['en', 'fr'], null, null);

        yield 'null color, no parent' => [null, ['en', 'fr'], null];
        yield 'black color, with parent' => ['000000', ['en', 'fr', 'de'], $parent];
        yield 'white color, no parent' => ['ffffff', ['en', 'fr', 'de', 'es'], null];
        yield 'custom color, with parent' => ['fefef0', ['en', 'fr', 'de', 'es', 'it'], $parent];
    }
}
