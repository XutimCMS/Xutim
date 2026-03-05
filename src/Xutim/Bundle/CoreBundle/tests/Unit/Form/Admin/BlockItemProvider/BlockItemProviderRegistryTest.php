<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Form\Admin\BlockItemProvider;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\BlockItemProviderInterface;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\BlockItemProviderRegistry;

final class BlockItemProviderRegistryTest extends TestCase
{
    public function testEmptyRegistry(): void
    {
        $registry = new BlockItemProviderRegistry([]);

        $this->assertSame([], $registry->all());
    }

    public function testRegistersProvidersByOptionClass(): void
    {
        $provider1 = $this->createProvider('App\Option\FooOption');
        $provider2 = $this->createProvider('App\Option\BarOption');

        $registry = new BlockItemProviderRegistry([$provider1, $provider2]);

        $all = $registry->all();
        $this->assertCount(2, $all);
        $this->assertArrayHasKey('App\Option\FooOption', $all);
        $this->assertArrayHasKey('App\Option\BarOption', $all);
        $this->assertSame($provider1, $all['App\Option\FooOption']);
        $this->assertSame($provider2, $all['App\Option\BarOption']);
    }

    public function testGet(): void
    {
        $provider = $this->createProvider('App\Option\FooOption');
        $registry = new BlockItemProviderRegistry([$provider]);

        $this->assertSame($provider, $registry->get('App\Option\FooOption'));
        $this->assertNull($registry->get('App\Option\NonExistent'));
    }

    public function testHas(): void
    {
        $provider = $this->createProvider('App\Option\FooOption');
        $registry = new BlockItemProviderRegistry([$provider]);

        $this->assertTrue($registry->has('App\Option\FooOption'));
        $this->assertFalse($registry->has('App\Option\NonExistent'));
    }

    public function testLastProviderWinsOnDuplicate(): void
    {
        $provider1 = $this->createProvider('App\Option\SameOption');
        $provider2 = $this->createProvider('App\Option\SameOption');

        $registry = new BlockItemProviderRegistry([$provider1, $provider2]);

        $this->assertCount(1, $registry->all());
        $this->assertSame($provider2, $registry->get('App\Option\SameOption'));
    }

    /**
     * @param class-string<BlockItemOption> $optionClass
     */
    private function createProvider(string $optionClass): BlockItemProviderInterface
    {
        $provider = $this->createStub(BlockItemProviderInterface::class);
        $provider->method('getOptionClass')->willReturn($optionClass);

        return $provider;
    }
}
