<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\LayoutFieldProvider;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\PageBlockItemOption;
use Xutim\CoreBundle\Repository\PageRepository;

final readonly class PageLayoutFieldProvider implements LayoutFieldProviderInterface
{
    public function __construct(private PageRepository $pageRepository)
    {
    }

    public function getOptionClass(): string
    {
        return PageBlockItemOption::class;
    }

    public function buildField(
        FormBuilderInterface|FormInterface $builder,
        string $fieldName,
        BlockItemOption $option
    ): void {
        $builder->add($fieldName, ChoiceType::class, [
            'choices' => array_flip($this->pageRepository->findAllPaths()),
            'label' => $this->humanize($fieldName),
            'placeholder' => new TranslatableMessage('select page', [], 'admin'),
            'required' => false,
            'attr' => ['data-controller' => 'tom-select'],
        ]);
    }

    public function denormalizeForForm(mixed $storedValue): mixed
    {
        return is_string($storedValue) && $storedValue !== '' ? $storedValue : null;
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
