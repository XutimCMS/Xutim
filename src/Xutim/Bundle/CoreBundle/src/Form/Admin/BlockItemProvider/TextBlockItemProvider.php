<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\BlockItemProvider;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Xutim\CoreBundle\Config\Layout\Block\Option\TextBlockItemOption;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;

final readonly class TextBlockItemProvider implements BlockItemProviderInterface
{
    public function getOptionClass(): string
    {
        return TextBlockItemOption::class;
    }

    /** @param FormBuilderInterface<mixed> $builder */
    public function buildFormFields(FormBuilderInterface $builder): void
    {
        $builder->add('text', TextType::class, [
            'label' => 'Text',
            'required' => false,
        ]);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapDataToForms(BlockItemDto $dto, array $forms): void
    {
        if (!array_key_exists('text', $forms)) {
            return;
        }
        $forms['text']->setData($dto->text);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapFormsToData(array $forms, BlockItemDto $dto): void
    {
        if (!array_key_exists('text', $forms)) {
            return;
        }
        /** @var string|null $text */
        $text = $forms['text']->getData();
        $dto->text = $text;
    }
}
