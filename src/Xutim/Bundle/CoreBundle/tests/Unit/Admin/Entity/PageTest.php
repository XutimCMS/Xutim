<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Admin\Entity;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Entity\Page;

final class PageTest extends TestCase
{
    /**
     * @dataProvider changeDataProvider
     */
    public function testChange(?string $color, array $locales, string $preTitle, string $title, string $subTitle, string $slug, array $content, string $locale, string $description, ?Page $parent): void
    {
        $page = new Page(null, '333333', [], $preTitle, $title, $subTitle, $slug, [], 'cs', '...', null, null);
        $page->change($color, $locales, $parent);
        $this->assertEquals($locales, $page->getLocales());
        $this->assertEquals($parent, $page->getParent());
    }

    public function changeDataProvider(): array
    {
        $parent = new Page(null, 'ffffff', ['en', 'fr'], 'Into title', 'Title', 'Sub title', 'title', [], 'en', 'Parent description', null, null);
        return [
            [null, ['en', 'fr'], 'Intro title', 'Title', 'Sub title', 'title', [], 'en', 'Description', null],
            ['000000', ['en', 'fr', 'de'], 'Intro title 2', 'Title 2', 'Sub title 2', 'title-2', [], 'fr', 'Description 2', $parent],
            ['ffffff', ['en', 'fr', 'de', 'es'], 'Intro title 3', 'Title 3', 'Sub title 3', 'title-3', [], 'de', 'Description 3', null],
            ['fefef0', ['en', 'fr', 'de', 'es', 'it'], 'Intro title 4', 'Title 4', 'Sub title 4', 'title-4', [], 'es', 'Description 4', $parent],
        ];
    }
}
