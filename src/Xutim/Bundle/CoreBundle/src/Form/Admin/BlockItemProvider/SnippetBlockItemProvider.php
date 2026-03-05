<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\BlockItemProvider;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Xutim\CoreBundle\Config\Layout\Block\Option\SnippetBlockItemOption;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

final readonly class SnippetBlockItemProvider implements BlockItemProviderInterface
{
    public function __construct(private string $snippetClass)
    {
    }

    public function getOptionClass(): string
    {
        return SnippetBlockItemOption::class;
    }

    /** @param FormBuilderInterface<mixed> $builder */
    public function buildFormFields(FormBuilderInterface $builder): void
    {
        $builder->add('snippet', EntityType::class, [
            'class' => $this->snippetClass,
            'label' => 'snippet',
            'required' => false,
        ]);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapDataToForms(BlockItemDto $dto, array $forms): void
    {
        if (!array_key_exists('snippet', $forms)) {
            return;
        }
        $forms['snippet']->setData($dto->snippet);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapFormsToData(array $forms, BlockItemDto $dto): void
    {
        if (!array_key_exists('snippet', $forms)) {
            return;
        }
        /** @var SnippetInterface|null $snippet */
        $snippet = $forms['snippet']->getData();
        $dto->snippet = $snippet;
    }
}
