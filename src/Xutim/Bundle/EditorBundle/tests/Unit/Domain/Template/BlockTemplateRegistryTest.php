<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Domain\Template;

use PHPUnit\Framework\TestCase;
use Xutim\EditorBundle\Domain\Template\BlockTemplateInterface;
use Xutim\EditorBundle\Domain\Template\BlockTemplateRegistry;
use Xutim\EditorBundle\Domain\Template\SlotDefinition;

final class BlockTemplateRegistryTest extends TestCase
{
    public function testCanInstantiateWithEmptyTemplates(): void
    {
        $registry = new BlockTemplateRegistry([]);

        $this->assertSame([], $registry->all());
        $this->assertSame([], $registry->getNames());
    }

    public function testCanInstantiateWithTemplates(): void
    {
        $template1 = $this->createTemplate('two_columns');
        $template2 = $this->createTemplate('three_columns');

        $registry = new BlockTemplateRegistry([$template1, $template2]);

        $this->assertCount(2, $registry->all());
        $this->assertSame(['two_columns', 'three_columns'], $registry->getNames());
    }

    public function testGet(): void
    {
        $template = $this->createTemplate('two_columns');
        $registry = new BlockTemplateRegistry([$template]);

        $this->assertSame($template, $registry->get('two_columns'));
        $this->assertNull($registry->get('nonexistent'));
    }

    public function testHas(): void
    {
        $template = $this->createTemplate('two_columns');
        $registry = new BlockTemplateRegistry([$template]);

        $this->assertTrue($registry->has('two_columns'));
        $this->assertFalse($registry->has('nonexistent'));
    }

    public function testAll(): void
    {
        $template1 = $this->createTemplate('two_columns');
        $template2 = $this->createTemplate('three_columns');
        $registry = new BlockTemplateRegistry([$template1, $template2]);

        $all = $registry->all();

        $this->assertArrayHasKey('two_columns', $all);
        $this->assertArrayHasKey('three_columns', $all);
        $this->assertSame($template1, $all['two_columns']);
        $this->assertSame($template2, $all['three_columns']);
    }

    public function testDuplicateNamesOverwrite(): void
    {
        $template1 = $this->createTemplate('same_name');
        $template2 = $this->createTemplate('same_name');

        $registry = new BlockTemplateRegistry([$template1, $template2]);

        $this->assertCount(1, $registry->all());
        $this->assertSame($template2, $registry->get('same_name'));
    }

    private function createTemplate(string $name): BlockTemplateInterface
    {
        $template = $this->createMock(BlockTemplateInterface::class);
        $template->method('getName')->willReturn($name);
        $template->method('getLabel')->willReturn(ucfirst(str_replace('_', ' ', $name)));
        $template->method('getDescription')->willReturn('Test template');
        $template->method('getSlots')->willReturn([
            new SlotDefinition(index: 0, label: 'Slot 1'),
        ]);
        $template->method('getSlotCount')->willReturn(1);

        return $template;
    }
}
