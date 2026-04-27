<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Config\Layout\Definition;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\ImageBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\TextBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinition;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinitionRegistry;

final class LayoutDefinitionRegistryTest extends TestCase
{
    public function testLookupByCode(): void
    {
        $pagePreview = $this->makeDefinition('page-preview', 'Page preview');
        $hero = $this->makeDefinition('hero', 'Hero');

        $registry = new LayoutDefinitionRegistry([$pagePreview, $hero]);

        self::assertSame($pagePreview, $registry->getByCode('page-preview'));
        self::assertSame($hero, $registry->getByCode('hero'));
        self::assertNull($registry->getByCode('missing'));
    }

    public function testHasChecksPresence(): void
    {
        $registry = new LayoutDefinitionRegistry([$this->makeDefinition('page-preview', 'PP')]);

        self::assertTrue($registry->has('page-preview'));
        self::assertFalse($registry->has('hero'));
    }

    public function testAllReturnsIndexedByCode(): void
    {
        $a = $this->makeDefinition('a', 'A');
        $b = $this->makeDefinition('b', 'B');

        $registry = new LayoutDefinitionRegistry([$a, $b]);

        self::assertSame(['a' => $a, 'b' => $b], $registry->all());
    }

    public function testDuplicateCodeThrows(): void
    {
        $first = $this->makeDefinition('same', 'First');
        $second = $this->makeDefinition('same', 'Second');

        $this->expectException(\LogicException::class);
        new LayoutDefinitionRegistry([$first, $second]);
    }

    public function testTextOptionIsTranslatable(): void
    {
        self::assertTrue((new TextBlockItemOption())->isTranslatable());
        self::assertFalse((new ImageBlockItemOption())->isTranslatable());
    }

    /**
     * @param array<string, BlockItemOption> $fields
     */
    private function makeDefinition(string $code, string $name, array $fields = []): LayoutDefinition
    {
        return new class ($code, $name, $fields) implements LayoutDefinition {
            /**
             * @param array<string, BlockItemOption> $fields
             */
            public function __construct(
                private readonly string $code,
                private readonly string $name,
                private readonly array $fields,
            ) {
            }

            public function getCode(): string
            {
                return $this->code;
            }

            public function getName(): string
            {
                return $this->name;
            }

            public function getFields(): array
            {
                return $this->fields;
            }

            public function getFieldDescriptions(): array
            {
                return [];
            }

            public function getTemplate(): string
            {
                return 'fake.html.twig';
            }

            public function getFormBodyTemplate(): ?string
            {
                return null;
            }

            public function getDescription(): string
            {
                return '';
            }

            public function getCategory(): string
            {
                return '';
            }

            public function getPreviewImage(): string
            {
                return '';
            }
        };
    }
}
