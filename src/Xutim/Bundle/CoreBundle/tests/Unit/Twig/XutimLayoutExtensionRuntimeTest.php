<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\PageBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\TextareaBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\TextBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinition;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinitionRegistry;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Service\AdminEditUrl\AdminEditUrlResolver;
use Xutim\CoreBundle\Service\XutimLayoutValueResolver;
use Xutim\CoreBundle\Twig\Runtime\XutimLayoutExtensionRuntime;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

final class XutimLayoutExtensionRuntimeTest extends TestCase
{
    private XutimLayoutExtensionRuntime $runtime;

    protected function setUp(): void
    {
        $logger = $this->createStub(LoggerInterface::class);
        $resolver = new XutimLayoutValueResolver(
            $this->createStub(MediaRepositoryInterface::class),
            $this->createStub(MediaFolderRepositoryInterface::class),
            $this->createStub(PageRepository::class),
            $this->createStub(ArticleRepository::class),
            $this->createStub(TagRepository::class),
            $this->createStub(SnippetRepositoryInterface::class),
            $logger,
        );

        $this->runtime = new XutimLayoutExtensionRuntime(
            new LayoutDefinitionRegistry([]),
            $resolver,
            $this->createStub(Environment::class),
            $logger,
            new AdminEditUrlResolver([]),
        );
    }

    public function testEditableOutsideEditModeReturnsEscapedPlainText(): void
    {
        $context = ['layout' => $this->layoutWithFields(['title' => new TextBlockItemOption()])];

        $result = $this->runtime->editable($context, 'title', '<script>alert(1)</script>');

        self::assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', $result);
    }

    public function testEditableOutsideEditModeWithMissingEditableFlag(): void
    {
        $context = ['layout' => $this->layoutWithFields(['title' => new TextBlockItemOption()])];

        $result = $this->runtime->editable($context, 'title', 'plain');

        self::assertSame('plain', $result);
    }

    public function testEditableInEditModeWrapsTextField(): void
    {
        $context = [
            'layout' => $this->layoutWithFields(['title' => new TextBlockItemOption()]),
            'editable' => true,
        ];

        $result = $this->runtime->editable($context, 'title', 'Hello');

        self::assertStringContainsString('data-xutim-editable="title"', $result);
        self::assertStringContainsString('data-xutim-placeholder="Title"', $result);
        self::assertStringContainsString('>Hello</span>', $result);
        self::assertStringNotContainsString('data-xutim-multiline', $result);
        self::assertStringNotContainsString('data-xutim-rich', $result);
    }

    public function testEditableInEditModeAddsMultilineForTextarea(): void
    {
        $context = [
            'layout' => $this->layoutWithFields(['body' => new TextareaBlockItemOption()]),
            'editable' => true,
        ];

        $result = $this->runtime->editable($context, 'body', 'one\ntwo');

        self::assertStringContainsString('data-xutim-multiline', $result);
    }

    public function testEditableInEditModeEscapesValueInsideWrapper(): void
    {
        $context = [
            'layout' => $this->layoutWithFields(['title' => new TextBlockItemOption()]),
            'editable' => true,
        ];

        $result = $this->runtime->editable($context, 'title', '<img src=x onerror=alert(1)>');

        self::assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $result);
        self::assertStringNotContainsString('<img', $result);
    }

    public function testEditableInEditModeEscapesFieldNameInAttributes(): void
    {
        $context = [
            'layout' => $this->layoutWithFields(['ti"tle' => new TextBlockItemOption()]),
            'editable' => true,
        ];

        $result = $this->runtime->editable($context, 'ti"tle', 'x');

        self::assertStringContainsString('data-xutim-editable="ti&quot;tle"', $result);
        self::assertStringNotContainsString('data-xutim-editable="ti"tle"', $result);
    }

    public function testEditableSkipsWrappingForNonInlineEditableField(): void
    {
        $context = [
            'layout' => $this->layoutWithFields(['link' => new PageBlockItemOption()]),
            'editable' => true,
        ];

        $result = $this->runtime->editable($context, 'link', 'home');

        self::assertSame('home', $result);
    }

    public function testEditableHumanizesCamelCaseFieldNameForPlaceholder(): void
    {
        $context = [
            'layout' => $this->layoutWithFields(['mainTitle' => new TextBlockItemOption()]),
            'editable' => true,
        ];

        $result = $this->runtime->editable($context, 'mainTitle', 'x');

        self::assertStringContainsString('data-xutim-placeholder="Main title"', $result);
    }

    public function testEditableCoercesScalarValuesToString(): void
    {
        $context = ['layout' => $this->layoutWithFields(['count' => new TextBlockItemOption()])];

        $result = $this->runtime->editable($context, 'count', 42);

        self::assertSame('42', $result);
    }

    public function testEditableTreatsNonScalarNonRichValueAsEmpty(): void
    {
        $context = [
            'layout' => $this->layoutWithFields(['title' => new TextBlockItemOption()]),
            'editable' => true,
        ];

        $result = $this->runtime->editable($context, 'title', ['unexpected' => 'array']);

        self::assertStringContainsString('data-xutim-editable="title"', $result);
        self::assertStringContainsString('></span>', $result);
    }

    public function testEditableHandlesUnknownFieldGracefully(): void
    {
        $context = [
            'layout' => $this->layoutWithFields(['title' => new TextBlockItemOption()]),
            'editable' => true,
        ];

        $result = $this->runtime->editable($context, 'missing', 'x');

        self::assertStringContainsString('data-xutim-editable="missing"', $result);
        self::assertStringNotContainsString('data-xutim-multiline', $result);
    }

    public function testEditableWithoutLayoutInContextStillEscapes(): void
    {
        $result = $this->runtime->editable([], 'title', '<b>x</b>');

        self::assertSame('&lt;b&gt;x&lt;/b&gt;', $result);
    }

    /**
     * @param array<string, BlockItemOption> $fields
     */
    private function layoutWithFields(array $fields): LayoutDefinition
    {
        return new class($fields) implements LayoutDefinition {
            /**
             * @param array<string, BlockItemOption> $fields
             */
            public function __construct(private readonly array $fields)
            {
            }

            public function getCode(): string
            {
                return 'test';
            }

            public function getName(): string
            {
                return 'Test';
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
                return 'test.html.twig';
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
