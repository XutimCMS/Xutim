<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Entity\Block;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\EditorBundle\Entity\Block\CodeBlock;
use Xutim\EditorBundle\Entity\ContentDraft;

final class CodeBlockTest extends TestCase
{
    private ContentDraft $draft;

    protected function setUp(): void
    {
        $translation = $this->createStub(ContentTranslationInterface::class);
        $this->draft = new ContentDraft($translation);
    }

    public function testCanInstantiate(): void
    {
        $code = "<?php\necho 'Hello';";
        $block = new CodeBlock($this->draft, $code, 'php');

        $this->assertSame('code', $block->getType());
        $this->assertSame($code, $block->getCode());
        $this->assertSame('php', $block->getLanguage());
    }

    public function testCanInstantiateWithoutLanguage(): void
    {
        $block = new CodeBlock($this->draft, 'some code');

        $this->assertSame('some code', $block->getCode());
        $this->assertNull($block->getLanguage());
    }

    public function testDefaultValues(): void
    {
        $block = new CodeBlock($this->draft);

        $this->assertSame('', $block->getCode());
        $this->assertNull($block->getLanguage());
    }

    public function testSetCode(): void
    {
        $block = new CodeBlock($this->draft);

        $block->setCode('console.log("test");');

        $this->assertSame('console.log("test");', $block->getCode());
    }

    public function testSetLanguage(): void
    {
        $block = new CodeBlock($this->draft);

        $block->setLanguage('javascript');

        $this->assertSame('javascript', $block->getLanguage());
    }

    public function testClearLanguage(): void
    {
        $block = new CodeBlock($this->draft, 'code', 'python');

        $block->setLanguage(null);

        $this->assertNull($block->getLanguage());
    }
}
