<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Infra\Layout;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Infra\Layout\LayoutFinder;
use Xutim\CoreBundle\Twig\ThemeFinder;

final class LayoutFinderTest extends TestCase
{
    public function testThemeWithoutLayoutDirectoryHasNoLayouts(): void
    {
        $themeFinder = $this->createStub(ThemeFinder::class);
        $themeFinder->method('getActiveThemePath')->willReturn('themes/acme');

        $finder = new LayoutFinder(
            $themeFinder,
            sys_get_temp_dir() . '/xutim-missing-templates',
            'layout/article',
            'layout/page',
            'layout/block',
            'layout/tag',
        );

        self::assertSame([], $finder->findTagLayouts());
    }
}
