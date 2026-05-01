<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\LayoutFieldProvider;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\SnippetBlockItemOption;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

final readonly class SnippetLayoutFieldProvider implements LayoutFieldProviderInterface
{
    public function __construct(
        private string $snippetClass,
        private SnippetRepositoryInterface $snippetRepository,
    ) {
    }

    public function getOptionClass(): string
    {
        return SnippetBlockItemOption::class;
    }

    public function buildField(
        FormBuilderInterface|FormInterface $builder,
        string $fieldName,
        BlockItemOption $option
    ): void {
        $builder->add($fieldName, EntityType::class, [
            'class' => $this->snippetClass,
            'label' => $this->humanize($fieldName),
            'required' => false,
            'choice_value' => 'id',
            'attr' => ['data-controller' => 'tom-select'],
        ]);
    }

    public function denormalizeForForm(mixed $storedValue): mixed
    {
        if (!is_string($storedValue) || $storedValue === '') {
            return null;
        }

        return $this->snippetRepository->findById($storedValue);
    }

    public function normalizeForStorage(mixed $formValue): mixed
    {
        if ($formValue instanceof SnippetInterface) {
            return $formValue->getId()->toRfc4122();
        }

        return null;
    }

    private function humanize(string $fieldName): string
    {
        return ucfirst(str_replace('_', ' ', $fieldName));
    }
}
