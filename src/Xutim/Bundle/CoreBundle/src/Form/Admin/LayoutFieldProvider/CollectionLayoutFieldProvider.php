<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\LayoutFieldProvider;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOptionCollection;

final readonly class CollectionLayoutFieldProvider implements LayoutFieldProviderInterface
{
    public function getOptionClass(): string
    {
        return BlockItemOptionCollection::class;
    }

    public function buildField(
        FormBuilderInterface|FormInterface $builder,
        string $fieldName,
        BlockItemOption $option
    ): void {
        if (!$option instanceof BlockItemOptionCollection) {
            throw new \LogicException('CollectionLayoutFieldProvider requires BlockItemOptionCollection');
        }

        $builder->add($fieldName, CollectionType::class, [
            'entry_type' => LayoutCollectionEntryType::class,
            'entry_options' => [
                'inner_option' => $option->getOption(),
                'label' => false,
            ],
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__name__',
            'required' => false,
            'label' => $this->humanize($fieldName),
            'block_prefix' => 'xutim_layout_collection',
        ]);
    }

    public function denormalizeForForm(mixed $storedValue): mixed
    {
        if (!is_array($storedValue)) {
            return [];
        }

        return array_values($storedValue);
    }

    public function normalizeForStorage(mixed $formValue): mixed
    {
        if (!is_array($formValue)) {
            return [];
        }

        return array_values($formValue);
    }

    private function humanize(string $fieldName): string
    {
        return ucfirst(str_replace('_', ' ', $fieldName));
    }
}
