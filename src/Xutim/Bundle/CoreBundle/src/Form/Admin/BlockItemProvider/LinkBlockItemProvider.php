<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\BlockItemProvider;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Xutim\CoreBundle\Config\Layout\Block\Option\LinkBlockItemOption;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;

final readonly class LinkBlockItemProvider implements BlockItemProviderInterface
{
    public function getOptionClass(): string
    {
        return LinkBlockItemOption::class;
    }

    /** @param FormBuilderInterface<mixed> $builder */
    public function buildFormFields(FormBuilderInterface $builder): void
    {
        $builder->add('link', TextType::class, [
            'label' => 'Link',
            'required' => false,
        ]);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapDataToForms(BlockItemDto $dto, array $forms): void
    {
        if (!array_key_exists('link', $forms)) {
            return;
        }
        $forms['link']->setData($dto->link);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapFormsToData(array $forms, BlockItemDto $dto): void
    {
        if (!array_key_exists('link', $forms)) {
            return;
        }
        /** @var string|null $link */
        $link = $forms['link']->getData();
        $dto->link = $link;
    }
}
