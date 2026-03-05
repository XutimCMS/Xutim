<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Form\Admin;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Xutim\CoreBundle\Config\Layout\Block\BlockOptionCollection;
use Xutim\CoreBundle\Config\Layout\Block\Option\TextBlockItemOption;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\BlockItemProviderRegistry;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\TextBlockItemProvider;
use Xutim\CoreBundle\Form\Admin\BlockItemType;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;

final class BlockItemTypeTest extends TypeTestCase
{
    private BlockItemProviderRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new BlockItemProviderRegistry([new TextBlockItemProvider()]);
        $this->dispatcher = $this->createStub(EventDispatcherInterface::class);

        parent::setUp();
    }

    protected function getTypes(): array
    {
        return [
            new BlockItemType($this->registry),
        ];
    }

    public function testBuildFormWithNoOptions(): void
    {
        $form = $this->factory->create(BlockItemType::class, null, [
            'block_options' => new BlockOptionCollection([]),
        ]);

        $this->assertTrue($form->has('submit'));
        $this->assertFalse($form->has('text'));
    }

    public function testBuildFormDelegatesToProviders(): void
    {
        $form = $this->factory->create(BlockItemType::class, null, [
            'block_options' => new BlockOptionCollection([
                TextBlockItemOption::class => new TextBlockItemOption(),
            ]),
        ]);

        $this->assertTrue($form->has('text'));
        $this->assertTrue($form->has('submit'));
    }

    public function testMapFormsToDataCreatesDto(): void
    {
        $form = $this->factory->create(BlockItemType::class, null, [
            'block_options' => new BlockOptionCollection([
                TextBlockItemOption::class => new TextBlockItemOption(),
            ]),
        ]);

        $form->submit(['text' => 'Hello world']);

        $this->assertTrue($form->isSynchronized());

        /** @var BlockItemDto $dto */
        $dto = $form->getData();
        $this->assertInstanceOf(BlockItemDto::class, $dto);
        $this->assertSame('Hello world', $dto->text);
    }

    public function testMapDataToFormsPopulatesForms(): void
    {
        $dto = new BlockItemDto(text: 'Existing text');

        $form = $this->factory->create(BlockItemType::class, $dto, [
            'block_options' => new BlockOptionCollection([
                TextBlockItemOption::class => new TextBlockItemOption(),
            ]),
        ]);

        $this->assertSame('Existing text', $form->get('text')->getData());
    }
}
