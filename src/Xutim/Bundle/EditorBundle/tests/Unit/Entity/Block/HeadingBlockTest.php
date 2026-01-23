<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Entity\Block;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\EditorBundle\Entity\Block\HeadingBlock;
use Xutim\EditorBundle\Entity\ContentDraft;

final class HeadingBlockTest extends TestCase
{
    private ContentDraft $draft;

    protected function setUp(): void
    {
        $translation = $this->createMock(ContentTranslationInterface::class);
        $this->draft = new ContentDraft($translation);
    }

    public function testCanInstantiate(): void
    {
        $block = new HeadingBlock($this->draft, 'My Heading', 2);

        $this->assertSame('heading', $block->getType());
        $this->assertSame('My Heading', $block->getHtml());
        $this->assertSame(2, $block->getLevel());
    }

    public function testDefaultLevel(): void
    {
        $block = new HeadingBlock($this->draft, 'Heading');

        $this->assertSame(2, $block->getLevel());
    }

    #[DataProvider('levelProvider')]
    public function testDifferentLevels(int $level): void
    {
        $block = new HeadingBlock($this->draft, 'Heading', $level);

        $this->assertSame($level, $block->getLevel());
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function levelProvider(): iterable
    {
        yield 'h1' => [1];
        yield 'h2' => [2];
        yield 'h3' => [3];
        yield 'h4' => [4];
        yield 'h5' => [5];
        yield 'h6' => [6];
    }

    public function testSetHtml(): void
    {
        $block = new HeadingBlock($this->draft, 'Initial');

        $block->setHtml('Updated');

        $this->assertSame('Updated', $block->getHtml());
    }

    public function testSetLevel(): void
    {
        $block = new HeadingBlock($this->draft, 'Heading', 2);

        $block->setLevel(3);

        $this->assertSame(3, $block->getLevel());
    }
}
