<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\LayoutFieldProvider;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\LinkBlockItemOption;

final readonly class LinkLayoutFieldProvider implements LayoutFieldProviderInterface
{
    public function getOptionClass(): string
    {
        return LinkBlockItemOption::class;
    }

    public function buildField(
        FormBuilderInterface|FormInterface $builder,
        string $fieldName,
        BlockItemOption $option
    ): void {
        $builder->add($fieldName, TextType::class, [
            'label' => $this->humanize($fieldName),
            'required' => false,
        ]);
    }

    public function denormalizeForForm(mixed $storedValue): mixed
    {
        return is_string($storedValue) ? $storedValue : null;
    }

    public function normalizeForStorage(mixed $formValue): mixed
    {
        return is_string($formValue) && $formValue !== '' ? $formValue : null;
    }

    private function humanize(string $fieldName): string
    {
        return ucfirst(str_replace('_', ' ', $fieldName));
    }
}
